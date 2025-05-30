<?php

namespace App\Services;

use App\Models\Lead;
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
     * Calculate commission amount for a lead
     */
    public function calculateCommission(Lead $lead)
    {
        if (!$lead->deal_value || !$lead->affiliatorProject) {
            return 0;
        }

        $project = $lead->affiliatorProject->project;
        
        if ($project->commission_type === 'percentage') {
            return ($lead->deal_value * $project->commission_value) / 100;
        }
        
        return $project->commission_value;
    }

    /**
     * Calculate and save commission when lead closes
     */
    public function calculateAndSaveCommission(Lead $lead)
    {
        if ($lead->commission_earned > 0) {
            // Commission already calculated
            return $lead->commission_earned;
        }

        DB::transaction(function () use ($lead) {
            $commissionAmount = $this->calculateCommission($lead);
            
            $lead->update(['commission_earned' => $commissionAmount]);

            // Create commission history
            CommissionHistory::create([
                'lead_id' => $lead->id,
                'user_id' => $lead->affiliatorProject->user_id,
                'project_id' => $lead->affiliatorProject->project_id,
                'amount' => $commissionAmount,
                'type' => 'earned',
                'description' => "Komisi dari lead: {$lead->customer_name}",
                'metadata' => [
                    'deal_value' => $lead->deal_value,
                    'commission_rate' => $lead->affiliatorProject->project->commission_value,
                    'commission_type' => $lead->affiliatorProject->project->commission_type
                ]
            ]);

            // Send notification to affiliator
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
                $lead->affiliatorProject->project_id,
                ['lead_id' => $lead->id, 'amount' => $commissionAmount]
            );
        });

        return $lead->commission_earned;
    }

    /**
     * Validate withdrawal request
     */
    public function validateWithdrawal($userId, $projectId, $amount)
    {
        $errors = [];
        $user = User::find($userId);

        if (!$user) {
            $errors[] = 'User tidak ditemukan';
            return $errors;
        }

        // Check minimum withdrawal amount
        $minAmount = SystemSetting::getValue('min_withdrawal_amount', 100000);
        if ($amount < $minAmount) {
            $errors[] = "Minimal penarikan Rp " . number_format($minAmount, 0, ',', '.');
        }

        // Check available commission
        $userService = app(UserService::class);
        $availableCommission = $userService->getAvailableCommission($user, $projectId);
        if ($amount > $availableCommission) {
            $errors[] = "Saldo komisi tidak mencukupi. Saldo tersedia: Rp " . number_format($availableCommission, 0, ',', '.');
        }

        // Check verified bank account
        $verifiedBankAccount = $user->bankAccounts()->verified()->first();
        if (!$verifiedBankAccount) {
            $errors[] = "Anda belum memiliki rekening bank yang terverifikasi.";
        }

        // Check pending withdrawal
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
     * Create withdrawal request
     */
    public function createWithdrawal($userId, $projectId, $bankAccountId, $amount, $notes = null)
    {
        $validationErrors = $this->validateWithdrawal($userId, $projectId, $amount);
        
        if (!empty($validationErrors)) {
            throw new \Exception(implode(' ', $validationErrors));
        }

        $withdrawal = DB::transaction(function () use ($userId, $projectId, $bankAccountId, $amount, $notes) {
            // Apply withdrawal fee if any
            $withdrawalFee = SystemSetting::getValue('commission_withdrawal_fee', 0);
            $netAmount = $amount - $withdrawalFee;

            $withdrawal = CommissionWithdrawal::create([
                'user_id' => $userId,
                'project_id' => $projectId,
                'bank_account_id' => $bankAccountId,
                'amount' => $amount,
                'notes' => $notes,
                'status' => 'pending'
            ]);

            $this->activityLogService->log(
                $userId,
                'request_withdrawal',
                "Request penarikan komisi Rp " . number_format($amount, 0, ',', '.'),
                $projectId,
                ['withdrawal_id' => $withdrawal->id, 'withdrawal_fee' => $withdrawalFee]
            );

            // Notify admins about new withdrawal request
            $user = User::find($userId);
            $project = \App\Models\Project::find($projectId);
            $admins = $project->admins;

            foreach ($admins as $admin) {
                $this->notificationService->createForUser(
                    $admin->id,
                    'Request Penarikan Baru',
                    "Penarikan komisi Rp " . number_format($amount, 0, ',', '.') . " dari {$user->name}",
                    'info',
                    ['withdrawal_id' => $withdrawal->id]
                );
            }

            return $withdrawal;
        });

        return $withdrawal;
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

            // Send notification to user
            $this->notificationService->createForUser(
                $withdrawal->user_id,
                'Penarikan Disetujui',
                "Penarikan komisi {$withdrawal->amount_formatted} telah disetujui",
                'success',
                ['withdrawal_id' => $withdrawal->id]
            );

            $this->activityLogService->log(
                $adminId,
                'approve_withdrawal',
                "Withdrawal {$withdrawal->amount_formatted} dari {$withdrawal->user->name} disetujui",
                $withdrawal->project_id,
                ['withdrawal_id' => $withdrawal->id]
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

            // Send notification to user
            $this->notificationService->createForUser(
                $withdrawal->user_id,
                'Penarikan Ditolak',
                "Penarikan komisi {$withdrawal->amount_formatted} ditolak: {$notes}",
                'error',
                ['withdrawal_id' => $withdrawal->id]
            );

            $this->activityLogService->log(
                $adminId,
                'reject_withdrawal',
                "Withdrawal {$withdrawal->amount_formatted} dari {$withdrawal->user->name} ditolak: {$notes}",
                $withdrawal->project_id,
                ['withdrawal_id' => $withdrawal->id]
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
            CommissionHistory::create([
                'lead_id' => null,
                'user_id' => $withdrawal->user_id,
                'project_id' => $withdrawal->project_id,
                'amount' => $withdrawal->amount,
                'type' => 'withdrawn',
                'description' => 'Penarikan komisi diproses',
                'metadata' => [
                    'withdrawal_id' => $withdrawal->id,
                    'bank_account' => $withdrawal->bankAccount->bank_name . ' - ' . $withdrawal->bankAccount->account_number
                ]
            ]);

            // Send notification to user
            $this->notificationService->createForUser(
                $withdrawal->user_id,
                'Penarikan Diproses',
                "Penarikan komisi {$withdrawal->amount_formatted} telah diproses dan dikirim ke rekening Anda",
                'success',
                ['withdrawal_id' => $withdrawal->id]
            );

            $this->activityLogService->log(
                $adminId,
                'process_withdrawal',
                "Withdrawal {$withdrawal->amount_formatted} dari {$withdrawal->user->name} telah diproses",
                $withdrawal->project_id,
                ['withdrawal_id' => $withdrawal->id]
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
            "Pembatalan withdrawal {$withdrawal->amount_formatted}",
            $withdrawal->project_id,
            ['withdrawal_id' => $withdrawal->id]
        );

        return true;
    }

    /**
     * Get commission statistics for user
     */
    public function getUserCommissionStats(User $user, $projectId = null)
    {
        $query = $user->commissionHistories();
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $stats = [
            'total_earned' => $query->clone()->where('type', 'earned')->sum('amount'),
            'total_withdrawn' => $query->clone()->where('type', 'withdrawn')->sum('amount'),
            'total_adjustments' => $query->clone()->where('type', 'adjustment')->sum('amount'),
            'pending_withdrawals' => $user->commissionWithdrawals()->pending()->sum('amount'),
            'this_month_earned' => $query->clone()->where('type', 'earned')->thisMonth()->sum('amount'),
            'this_year_earned' => $query->clone()->where('type', 'earned')->thisYear()->sum('amount')
        ];

        $stats['available_balance'] = $stats['total_earned'] - $stats['total_withdrawn'] + $stats['total_adjustments'];

        return $stats;
    }

    /**
     * Get commission leaderboard
     */
    public function getCommissionLeaderboard($projectId = null, $period = 'month', $limit = 10)
    {
        $query = CommissionHistory::with('user:id,name,email')
            ->where('type', 'earned');

        if ($projectId) {
            $query->where('project_id', $projectId);
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
        $adjustment = CommissionHistory::create([
            'lead_id' => null,
            'user_id' => $userId,
            'project_id' => $projectId,
            'amount' => $amount,
            'type' => 'adjustment',
            'description' => $description,
            'metadata' => [
                'adjusted_by' => $adminId,
                'adjustment_reason' => $description
            ]
        ]);

        $user = User::find($userId);
        $adjustmentType = $amount > 0 ? 'penambahan' : 'pengurangan';
        
        // Send notification
        $this->notificationService->createForUser(
            $userId,
            'Penyesuaian Komisi',
            "Komisi Anda disesuaikan ({$adjustmentType}): Rp " . number_format(abs($amount), 0, ',', '.') . ". {$description}",
            $amount > 0 ? 'success' : 'warning',
            ['adjustment_id' => $adjustment->id]
        );

        $this->activityLogService->log(
            $adminId,
            'commission_adjustment',
            "Penyesuaian komisi untuk {$user->name}: Rp " . number_format($amount, 0, ',', '.') . ". {$description}",
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
        $query = CommissionHistory::with(['user:id,name,email', 'project:id,name', 'lead:id,customer_name'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        if ($projectId) {
            $query->where('project_id', $projectId);
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