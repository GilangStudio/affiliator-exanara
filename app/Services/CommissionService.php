<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Unit;
use App\Models\Project;
use App\Models\CommissionHistory;
use App\Models\CommissionWithdrawal;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    protected $notificationService;
    protected $activityLogService;

    public function __construct(
        NotificationService $notificationService,
        ActivityLogService $activityLogService
    ) {
        $this->notificationService = $notificationService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * Calculate commission amount for a lead based on unit
     */
    public function calculateCommission(Lead $lead)
    {
        if (!$lead->unit || !$lead->affiliatorProject) {
            return 0;
        }

        $unit = $lead->unit;
        
        if ($unit->commission_type === 'percentage') {
            $baseAmount = $lead->deal_value ?? $unit->price;
            return ($baseAmount * $unit->commission_value) / 100;
        }
        
        return $unit->commission_value;
    }

    /**
     * Calculate and save commission when lead closes
     */
    public function calculateAndSaveCommission(Lead $lead)
    {
        if ($lead->commission_earned > 0) {
            return $lead->commission_earned;
        }

        DB::transaction(function () use ($lead) {
            $commissionAmount = $this->calculateCommission($lead);
            
            $lead->update(['commission_earned' => $commissionAmount]);

            // Create commission history
            CommissionHistory::create([
                'lead_id' => $lead->id,
                'user_id' => $lead->affiliatorProject->user_id,
                'unit_id' => $lead->unit_id,
                'amount' => $commissionAmount,
                'type' => 'earned',
                'description' => "Komisi dari lead: {$lead->customer_name}",
                'metadata' => [
                    'deal_value' => $lead->deal_value,
                    'unit_price' => $lead->unit->price,
                    'commission_rate' => $lead->unit->commission_value,
                    'commission_type' => $lead->unit->commission_type
                ]
            ]);

            // Send notification
            $this->notificationService->createForUser(
                $lead->affiliatorProject->user_id,
                'Komisi Baru!',
                "Anda mendapat komisi Rp " . number_format($commissionAmount, 0, ',', '.') . " dari lead {$lead->customer_name}",
                'success',
                ['lead_id' => $lead->id, 'amount' => $commissionAmount]
            );

            // Log activity
            $this->activityLogService->log(
                $lead->affiliatorProject->user_id,
                'commission_earned',
                "Komisi diperoleh dari lead {$lead->customer_name}: Rp " . number_format($commissionAmount, 0, ',', '.'),
                $lead->unit->project_id,
                ['lead_id' => $lead->id, 'unit_id' => $lead->unit_id, 'amount' => $commissionAmount]
            );
        });

        return $lead->commission_earned;
    }

    /**
     * Validate withdrawal request (now per project)
     */
    public function validateWithdrawal($userId, $projectId, $amount)
    {
        $errors = [];
        $user = User::find($userId);

        if (!$user) {
            $errors[] = 'User tidak ditemukan';
            return $errors;
        }

        $project = Project::find($projectId);
        if (!$project) {
            $errors[] = 'Project tidak ditemukan';
            return $errors;
        }

        // Check minimum withdrawal amount
        $minAmount = SystemSetting::getValue('min_withdrawal_amount', 100000);
        if ($amount < $minAmount) {
            $errors[] = "Minimal penarikan Rp " . number_format($minAmount, 0, ',', '.');
        }

        // Check available commission for this project (calculated from all units in project)
        $availableCommission = $this->getAvailableCommissionByProject($user, $projectId);
        if ($amount > $availableCommission) {
            $errors[] = "Saldo komisi tidak mencukupi. Saldo tersedia: Rp " . number_format($availableCommission, 0, ',', '.');
        }

        // Check verified bank account
        $verifiedBankAccount = $user->bankAccounts()->verified()->first();
        if (!$verifiedBankAccount) {
            $errors[] = "Anda belum memiliki rekening bank yang terverifikasi.";
        }

        // Check pending withdrawal for this project
        $pendingWithdrawal = CommissionWithdrawal::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->pending()
            ->exists();
        
        if ($pendingWithdrawal) {
            $errors[] = "Anda masih memiliki penarikan yang sedang diproses untuk project ini.";
        }

        // Check user is active
        if (!$user->is_active) {
            $errors[] = "Akun Anda tidak aktif.";
        }

        return $errors;
    }

    /**
     * Create withdrawal request (now per project)
     */
    public function createWithdrawal($userId, $projectId, $bankAccountId, $amount, $notes = null)
    {
        $validationErrors = $this->validateWithdrawal($userId, $projectId, $amount);
        
        if (!empty($validationErrors)) {
            throw new \Exception(implode(' ', $validationErrors));
        }

        $withdrawal = DB::transaction(function () use ($userId, $projectId, $bankAccountId, $amount, $notes) {
            $withdrawal = CommissionWithdrawal::create([
                'user_id' => $userId,
                'project_id' => $projectId,
                'bank_account_id' => $bankAccountId,
                'amount' => $amount,
                'notes' => $notes,
                'status' => 'pending'
            ]);

            $project = Project::find($projectId);
            
            $this->activityLogService->log(
                $userId,
                'request_withdrawal',
                "Request penarikan komisi Rp " . number_format($amount, 0, ',', '.') . " untuk project {$project->name}",
                $project->id,
                ['withdrawal_id' => $withdrawal->id, 'project_id' => $projectId]
            );

            // Notify project admins
            $user = User::find($userId);
            $admins = $project->admins;

            foreach ($admins as $admin) {
                $this->notificationService->createForUser(
                    $admin->id,
                    'Request Penarikan Baru',
                    "Penarikan komisi Rp " . number_format($amount, 0, ',', '.') . " dari {$user->name} untuk project {$project->name}",
                    'info',
                    ['withdrawal_id' => $withdrawal->id]
                );
            }

            return $withdrawal;
        });

        return $withdrawal;
    }

    /**
     * Get available commission by project (calculated from all units in project)
     */
    public function getAvailableCommissionByProject(User $user, $projectId)
    {
        // Get all units in this project
        $unitIds = Unit::where('project_id', $projectId)->pluck('id');
        
        $earned = CommissionHistory::where('user_id', $user->id)
            ->whereIn('unit_id', $unitIds)
            ->where('type', 'earned')
            ->sum('amount');
            
        $withdrawn = CommissionHistory::where('user_id', $user->id)
            ->whereIn('unit_id', $unitIds)
            ->where('type', 'withdrawn')
            ->sum('amount');
            
        $adjustments = CommissionHistory::where('user_id', $user->id)
            ->whereIn('unit_id', $unitIds)
            ->where('type', 'adjustment')
            ->sum('amount');
        
        return $earned - $withdrawn + $adjustments;
    }

    /**
     * Get commission statistics for user (can be filtered by project)
     */
    public function getUserCommissionStats(User $user, $projectId = null)
    {
        if ($projectId) {
            // Get units for specific project
            $unitIds = Unit::where('project_id', $projectId)->pluck('id');
            $query = $user->commissionHistories()->whereIn('unit_id', $unitIds);
            $withdrawalQuery = $user->commissionWithdrawals()->where('project_id', $projectId);
        } else {
            $query = $user->commissionHistories();
            $withdrawalQuery = $user->commissionWithdrawals();
        }

        $stats = [
            'total_earned' => $query->clone()->where('type', 'earned')->sum('amount'),
            'total_withdrawn' => $query->clone()->where('type', 'withdrawn')->sum('amount'),
            'total_adjustments' => $query->clone()->where('type', 'adjustment')->sum('amount'),
            'pending_withdrawals' => $withdrawalQuery->pending()->sum('amount'),
            'this_month_earned' => $query->clone()->where('type', 'earned')->thisMonth()->sum('amount'),
            'this_year_earned' => $query->clone()->where('type', 'earned')->thisYear()->sum('amount')
        ];

        $stats['available_balance'] = $stats['total_earned'] - $stats['total_withdrawn'] + $stats['total_adjustments'];

        return $stats;
    }

    /**
     * Approve withdrawal request
     */
    public function approveWithdrawal(CommissionWithdrawal $withdrawal, $adminId, $notes = null)
    {
        if ($withdrawal->status !== 'pending') {
            throw new \Exception('Penarikan tidak dapat disetujui karena status: ' . $withdrawal->status);
        }

        DB::transaction(function () use ($withdrawal, $adminId, $notes) {
            $withdrawal->update([
                'status' => 'approved',
                'processed_by' => $adminId,
                'processed_at' => now(),
                'admin_notes' => $notes
            ]);

            $this->notificationService->createForUser(
                $withdrawal->user_id,
                'Penarikan Disetujui',
                "Penarikan komisi {$withdrawal->amount_formatted} untuk project {$withdrawal->project->name} telah disetujui",
                'success',
                ['withdrawal_id' => $withdrawal->id]
            );

            $this->activityLogService->log(
                $adminId,
                'approve_withdrawal',
                "Withdrawal {$withdrawal->amount_formatted} dari {$withdrawal->user->name} untuk project {$withdrawal->project->name} disetujui",
                $withdrawal->project_id,
                ['withdrawal_id' => $withdrawal->id, 'project_id' => $withdrawal->project_id]
            );
        });

        return $withdrawal;
    }

    /**
     * Reject withdrawal request
     */
    public function rejectWithdrawal(CommissionWithdrawal $withdrawal, $adminId, $notes)
    {
        if ($withdrawal->status !== 'pending') {
            throw new \Exception('Penarikan tidak dapat ditolak karena status: ' . $withdrawal->status);
        }

        if (empty($notes)) {
            throw new \Exception('Alasan penolakan harus diisi');
        }

        DB::transaction(function () use ($withdrawal, $adminId, $notes) {
            $withdrawal->update([
                'status' => 'rejected',
                'processed_by' => $adminId,
                'processed_at' => now(),
                'admin_notes' => $notes
            ]);

            $this->notificationService->createForUser(
                $withdrawal->user_id,
                'Penarikan Ditolak',
                "Penarikan komisi {$withdrawal->amount_formatted} untuk project {$withdrawal->project->name} ditolak: {$notes}",
                'error',
                ['withdrawal_id' => $withdrawal->id]
            );

            $this->activityLogService->log(
                $adminId,
                'reject_withdrawal',
                "Withdrawal {$withdrawal->amount_formatted} dari {$withdrawal->user->name} untuk project {$withdrawal->project->name} ditolak: {$notes}",
                $withdrawal->project_id,
                ['withdrawal_id' => $withdrawal->id, 'project_id' => $withdrawal->project_id]
            );
        });

        return $withdrawal;
    }

    /**
     * Process withdrawal (mark as processed/paid)
     */
    public function processWithdrawal(CommissionWithdrawal $withdrawal, $adminId)
    {
        if ($withdrawal->status !== 'approved') {
            throw new \Exception('Penarikan harus disetujui terlebih dahulu');
        }

        DB::transaction(function () use ($withdrawal, $adminId) {
            $withdrawal->update([
                'status' => 'processed',
                'processed_by' => $adminId,
                'processed_at' => now()
            ]);

            // Create commission history for withdrawal
            // We need to distribute this withdrawal across the units proportionally
            $projectUnits = Unit::where('project_id', $withdrawal->project_id)->pluck('id');
            
            // For simplicity, we'll record one withdrawal entry for the main unit or distribute evenly
            // This depends on business logic - here we'll record to the first unit
            $firstUnit = $projectUnits->first();
            
            if ($firstUnit) {
                CommissionHistory::create([
                    'lead_id' => null,
                    'user_id' => $withdrawal->user_id,
                    'unit_id' => $firstUnit,
                    'amount' => $withdrawal->amount,
                    'type' => 'withdrawn',
                    'description' => 'Penarikan komisi diproses',
                    'metadata' => [
                        'withdrawal_id' => $withdrawal->id,
                        'project_id' => $withdrawal->project_id,
                        'bank_account' => $withdrawal->bankAccount->bank_name . ' - ' . $withdrawal->bankAccount->account_number
                    ]
                ]);
            }

            $this->notificationService->createForUser(
                $withdrawal->user_id,
                'Penarikan Diproses',
                "Penarikan komisi {$withdrawal->amount_formatted} untuk project {$withdrawal->project->name} telah diproses",
                'success',
                ['withdrawal_id' => $withdrawal->id]
            );

            $this->activityLogService->log(
                $adminId,
                'process_withdrawal',
                "Withdrawal {$withdrawal->amount_formatted} dari {$withdrawal->user->name} untuk project {$withdrawal->project->name} telah diproses",
                $withdrawal->project_id,
                ['withdrawal_id' => $withdrawal->id, 'project_id' => $withdrawal->project_id]
            );
        });

        return $withdrawal;
    }

    /**
     * Cancel withdrawal request (by user)
     */
    public function cancelWithdrawal(CommissionWithdrawal $withdrawal, $userId)
    {
        if ($withdrawal->user_id !== $userId) {
            throw new \Exception('Anda tidak memiliki akses untuk membatalkan penarikan ini');
        }

        if ($withdrawal->status !== 'pending') {
            throw new \Exception('Penarikan tidak dapat dibatalkan karena status: ' . $withdrawal->status_label);
        }

        $withdrawal->delete();

        $this->activityLogService->log(
            $userId,
            'cancel_withdrawal',
            "Pembatalan withdrawal {$withdrawal->amount_formatted} untuk project {$withdrawal->project->name}",
            $withdrawal->project_id,
            ['withdrawal_id' => $withdrawal->id]
        );

        return true;
    }

    /**
     * Get commission leaderboard
     */
    public function getCommissionLeaderboard($projectId = null, $period = 'month', $limit = 10)
    {
        $query = CommissionHistory::with('user:id,name,email')
            ->where('type', 'earned');

        if ($projectId) {
            $unitIds = Unit::where('project_id', $projectId)->pluck('id');
            $query->whereIn('unit_id', $unitIds);
        }

        // Apply period filter
        switch ($period) {
            case 'week':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->thisMonth();
                break;
            case 'year':
                $query->thisYear();
                break;
        }

        return $query->selectRaw('user_id, SUM(amount) as total_commission, COUNT(*) as total_leads')
            ->groupBy('user_id')
            ->orderBy('total_commission', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Create commission adjustment (manual)
     */
    public function createCommissionAdjustment($userId, $projectId, $amount, $description, $adminId)
    {
        // Get first unit in project for recording the adjustment
        $firstUnit = Unit::where('project_id', $projectId)->first();
        
        if (!$firstUnit) {
            throw new \Exception('Project tidak memiliki unit');
        }

        $adjustment = CommissionHistory::create([
            'lead_id' => null,
            'user_id' => $userId,
            'unit_id' => $firstUnit->id,
            'amount' => $amount,
            'type' => 'adjustment',
            'description' => $description,
            'metadata' => [
                'adjusted_by' => $adminId,
                'project_id' => $projectId,
                'adjustment_reason' => $description
            ]
        ]);

        $user = User::find($userId);
        $project = Project::find($projectId);
        $adjustmentType = $amount > 0 ? 'penambahan' : 'pengurangan';
        
        // Send notification
        $this->notificationService->createForUser(
            $userId,
            'Penyesuaian Komisi',
            "Komisi Anda disesuaikan ({$adjustmentType}): Rp " . number_format(abs($amount), 0, ',', '.') . " untuk project {$project->name}. {$description}",
            $amount > 0 ? 'success' : 'warning',
            ['adjustment_id' => $adjustment->id]
        );

        $this->activityLogService->log(
            $adminId,
            'commission_adjustment',
            "Penyesuaian komisi untuk {$user->name}: Rp " . number_format($amount, 0, ',', '.') . " untuk project {$project->name}. {$description}",
            $projectId,
            ['adjustment_id' => $adjustment->id, 'user_id' => $userId]
        );

        return $adjustment;
    }

    /**
     * Generate commission report
     */
    public function generateCommissionReport($startDate, $endDate, $projectId = null)
    {
        $query = CommissionHistory::with(['user:id,name,email', 'unit.project:id,name', 'lead:id,customer_name'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($projectId) {
            $unitIds = Unit::where('project_id', $projectId)->pluck('id');
            $query->whereIn('unit_id', $unitIds);
        }

        $commissions = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_earned' => $commissions->where('type', 'earned')->sum('amount'),
            'total_withdrawn' => $commissions->where('type', 'withdrawn')->sum('amount'),
            'total_adjustments' => $commissions->where('type', 'adjustment')->sum('amount'),
            'total_leads' => $commissions->where('type', 'earned')->count(),
            'unique_affiliators' => $commissions->where('type', 'earned')->pluck('user_id')->unique()->count(),
        ];

        return [
            'summary' => $summary,
            'details' => $commissions,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ];
    }
}