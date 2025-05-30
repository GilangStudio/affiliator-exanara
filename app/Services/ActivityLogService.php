<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Project;
use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    /**
     * Log user activity
     */
    public function log($userId, $action, $description, $projectId = null, $properties = null)
    {
        return ActivityLog::create([
            'user_id' => $userId,
            'project_id' => $projectId,
            'action' => $action,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log authentication activities
     */
    public function logAuth($userId, $action, $description = null)
    {
        $descriptions = [
            'login' => 'User berhasil login',
            'logout' => 'User logout dari sistem',
            'login_failed' => 'Percobaan login gagal',
            'password_reset' => 'Password berhasil direset',
            'password_change' => 'Password berhasil diubah'
        ];

        $finalDescription = $description ?: ($descriptions[$action] ?? $description);

        return $this->log($userId, $action, $finalDescription);
    }

    /**
     * Log lead activities
     */
    public function logLeadActivity($userId, $action, $leadId, $projectId, $description = null, $properties = null)
    {
        $lead = Lead::find($leadId);
        $customerName = $lead ? $lead->customer_name : 'Unknown';

        $descriptions = [
            'add_lead' => "Lead baru ditambahkan: {$customerName}",
            'verify_lead' => "Lead {$customerName} diverifikasi",
            'reject_lead' => "Lead {$customerName} ditolak",
            'send_to_crm' => "Lead {$customerName} dikirim ke CRM",
            'update_crm_status' => "Status CRM lead {$customerName} diperbarui"
        ];

        $finalDescription = $description ?: ($descriptions[$action] ?? $description);
        $finalProperties = array_merge(['lead_id' => $leadId], $properties ?: []);

        return $this->log($userId, $action, $finalDescription, $projectId, $finalProperties);
    }

    /**
     * Log commission activities
     */
    public function logCommissionActivity($userId, $action, $amount, $projectId = null, $description = null, $properties = null)
    {
        $formattedAmount = 'Rp ' . number_format($amount, 0, ',', '.');

        $descriptions = [
            'commission_earned' => "Komisi diperoleh: {$formattedAmount}",
            'request_withdrawal' => "Request penarikan komisi: {$formattedAmount}",
            'approve_withdrawal' => "Penarikan komisi disetujui: {$formattedAmount}",
            'reject_withdrawal' => "Penarikan komisi ditolak: {$formattedAmount}",
            'process_withdrawal' => "Penarikan komisi diproses: {$formattedAmount}",
            'commission_adjustment' => "Penyesuaian komisi: {$formattedAmount}"
        ];

        $finalDescription = $description ?: ($descriptions[$action] ?? $description);
        $finalProperties = array_merge(['amount' => $amount], $properties ?: []);

        return $this->log($userId, $action, $finalDescription, $projectId, $finalProperties);
    }

    /**
     * Log profile activities
     */
    public function logProfileActivity($userId, $action, $description = null, $properties = null)
    {
        $descriptions = [
            'update_profile' => 'Profil diperbarui',
            'update_profile_photo' => 'Foto profil diperbarui',
            'delete_profile_photo' => 'Foto profil dihapus',
            'change_password' => 'Password diubah'
        ];

        $finalDescription = $description ?: ($descriptions[$action] ?? $description);

        return $this->log($userId, $action, $finalDescription, null, $properties);
    }

    /**
     * Log affiliator project activities
     */
    public function logAffiliatorProjectActivity($userId, $action, $projectId, $description = null, $properties = null)
    {
        $project = Project::find($projectId);
        $projectName = $project ? $project->name : 'Unknown Project';

        $descriptions = [
            'join_project' => "Bergabung dengan project: {$projectName}",
            'upload_ktp' => "Upload KTP untuk project: {$projectName}",
            'accept_terms' => "Menyetujui syarat dan ketentuan project: {$projectName}",
            'digital_signature' => "Tanda tangan digital untuk project: {$projectName}",
            'suspend_affiliator' => "Affiliator disuspend dari project: {$projectName}",
            'activate_affiliator' => "Affiliator diaktifkan untuk project: {$projectName}"
        ];

        $finalDescription = $description ?: ($descriptions[$action] ?? $description);

        return $this->log($userId, $action, $finalDescription, $projectId, $properties);
    }

    /**
     * Log bank account activities
     */
    public function logBankAccountActivity($userId, $action, $bankAccountId, $description = null, $properties = null)
    {
        $bankAccount = BankAccount::find($bankAccountId);
        $bankInfo = $bankAccount ? "{$bankAccount->bank_name} - {$bankAccount->masked_account_number}" : 'Unknown';

        $descriptions = [
            'add_bank_account' => "Rekening bank ditambahkan: {$bankInfo}",
            'verify_bank_account' => "Rekening bank diverifikasi: {$bankInfo}",
            'reject_bank_account' => "Rekening bank ditolak: {$bankInfo}",
            'update_bank_account' => "Rekening bank diperbarui: {$bankInfo}",
            'delete_bank_account' => "Rekening bank dihapus: {$bankInfo}"
        ];

        $finalDescription = $description ?: ($descriptions[$action] ?? $description);
        $finalProperties = array_merge(['bank_account_id' => $bankAccountId], $properties ?: []);

        return $this->log($userId, $action, $finalDescription, null, $finalProperties);
    }

    /**
     * Log support ticket activities
     */
    public function logSupportTicketActivity($userId, $action, $ticketId, $description = null, $properties = null)
    {
        $ticket = SupportTicket::find($ticketId);
        $ticketInfo = $ticket ? "#{$ticket->ticket_number}" : "#Unknown";

        $descriptions = [
            'create_ticket' => "Tiket support dibuat: {$ticketInfo}",
            'assign_ticket' => "Tiket support ditugaskan: {$ticketInfo}",
            'resolve_ticket' => "Tiket support diselesaikan: {$ticketInfo}",
            'close_ticket' => "Tiket support ditutup: {$ticketInfo}",
            'reopen_ticket' => "Tiket support dibuka kembali: {$ticketInfo}"
        ];

        $finalDescription = $description ?: ($descriptions[$action] ?? $description);
        $finalProperties = array_merge(['ticket_id' => $ticketId], $properties ?: []);

        return $this->log($userId, $action, $finalDescription, null, $finalProperties);
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary($userId, $days = 30)
    {
        return ActivityLog::where('user_id', $userId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
    }

    /**
     * Get project activity summary
     */
    public function getProjectActivitySummary($projectId, $days = 30)
    {
        return ActivityLog::where('project_id', $projectId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();
    }

    /**
     * Get most active users
     */
    public function getMostActiveUsers($limit = 10, $days = 30)
    {
        return ActivityLog::with('user:id,name,email,role')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('user_id, COUNT(*) as activity_count')
            ->groupBy('user_id')
            ->orderBy('activity_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 50, $userId = null, $projectId = null, $actions = null)
    {
        $query = ActivityLog::with(['user:id,name,email,role', 'project:id,name'])
            ->orderBy('created_at', 'desc');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        if ($actions && is_array($actions)) {
            $query->whereIn('action', $actions);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get activity statistics
     */
    public function getActivityStats($startDate = null, $endDate = null, $projectId = null)
    {
        $query = ActivityLog::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } else {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $stats = [
            'total_activities' => $query->count(),
            'unique_users' => $query->distinct('user_id')->count('user_id'),
            'activities_by_action' => $query->clone()
                ->selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->pluck('count', 'action')
                ->toArray(),
            'activities_by_day' => $query->clone()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->pluck('count', 'date')
                ->toArray(),
            'top_active_users' => $query->clone()
                ->with('user:id,name,email')
                ->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
        ];

        return $stats;
    }

    /**
     * Search activities
     */
    public function searchActivities(array $filters, $perPage = 15)
    {
        $query = ActivityLog::with(['user:id,name,email,role', 'project:id,name'])
            ->orderBy('created_at', 'desc');

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by project
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        // Filter by action
        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        // Filter by multiple actions
        if (!empty($filters['actions']) && is_array($filters['actions'])) {
            $query->whereIn('action', $filters['actions']);
        }

        // Search in description
        if (!empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        // Filter by date range
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        // Filter by IP address
        if (!empty($filters['ip_address'])) {
            $query->where('ip_address', $filters['ip_address']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Clean old activity logs
     */
    public function cleanOldLogs($days = 365)
    {
        return ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Export activities to CSV
     */
    public function exportToCsv(array $filters = [])
    {
        $query = ActivityLog::with(['user:id,name,email', 'project:id,name'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->where('created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        $activities = $query->get();

        $csvData = [];
        $csvData[] = ['Tanggal', 'User', 'Project', 'Aksi', 'Deskripsi', 'IP Address'];

        foreach ($activities as $activity) {
            $csvData[] = [
                $activity->created_at->format('Y-m-d H:i:s'),
                $activity->user ? $activity->user->name : 'Unknown',
                $activity->project ? $activity->project->name : '-',
                $activity->action_label,
                $activity->description,
                $activity->ip_address ?: '-'
            ];
        }

        return $csvData;
    }

    /**
     * Get user login history
     */
    public function getUserLoginHistory($userId, $limit = 20)
    {
        return ActivityLog::where('user_id', $userId)
            ->whereIn('action', ['login', 'logout', 'login_failed'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Detect suspicious activities
     */
    public function detectSuspiciousActivities($days = 7)
    {
        $suspiciousActivities = [];

        // Multiple failed login attempts
        $failedLogins = ActivityLog::where('action', 'login_failed')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('ip_address, COUNT(*) as attempts')
            ->groupBy('ip_address')
            ->having('attempts', '>', 5)
            ->get();

        foreach ($failedLogins as $failedLogin) {
            $suspiciousActivities[] = [
                'type' => 'multiple_failed_logins',
                'ip_address' => $failedLogin->ip_address,
                'attempts' => $failedLogin->attempts,
                'severity' => 'high'
            ];
        }

        // Unusual activity volume
        $highVolumeUsers = ActivityLog::where('created_at', '>=', now()->subDays($days))
            ->selectRaw('user_id, COUNT(*) as activity_count')
            ->groupBy('user_id')
            ->having('activity_count', '>', 1000)
            ->with('user:id,name,email')
            ->get();

        foreach ($highVolumeUsers as $user) {
            $suspiciousActivities[] = [
                'type' => 'high_activity_volume',
                'user' => $user->user,
                'activity_count' => $user->activity_count,
                'severity' => 'medium'
            ];
        }

        return $suspiciousActivities;
    }
}