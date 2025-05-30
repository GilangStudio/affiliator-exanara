<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $whatsappService;
    protected $emailService;

    public function __construct(
        WhatsAppService $whatsappService = null,
        EmailService $emailService = null
    ) {
        $this->whatsappService = $whatsappService;
        $this->emailService = $emailService;
    }

    /**
     * Create notification for specific user
     */
    public function createForUser($userId, $title, $message, $type = 'info', $data = null)
    {
        $notification = Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'data' => $data
        ]);

        // Send external notifications based on settings
        $this->sendExternalNotifications($userId, $title, $message, $type);

        return $notification;
    }

    /**
     * Broadcast notification to multiple users
     */
    public function broadcast($userIds, $title, $message, $type = 'info', $data = null)
    {
        if (empty($userIds)) {
            return false;
        }

        $notifications = [];
        $now = now();

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'data' => json_encode($data),
                'created_at' => $now,
                'updated_at' => $now
            ];
        }
        
        $result = Notification::insert($notifications);

        // Send external notifications
        foreach ($userIds as $userId) {
            $this->sendExternalNotifications($userId, $title, $message, $type);
        }

        return $result;
    }

    /**
     * Send notification to all project admins
     */
    public function notifyProjectAdmins($projectId, $title, $message, $type = 'info', $data = null)
    {
        $project = \App\Models\Project::find($projectId);
        if (!$project) {
            return false;
        }

        $adminIds = $project->admins->pluck('id')->toArray();

        return $this->broadcast($adminIds, $title, $message, $type, $data);
    }

    /**
     * Send notification to all affiliators of a project
     */
    public function notifyProjectAffiliators($projectId, $title, $message, $type = 'info', $data = null)
    {
        $affiliatorIds = \App\Models\AffiliatorProject::where('project_id', $projectId)
            ->where('status', 'active')
            ->pluck('user_id')
            ->toArray();

        return $this->broadcast($affiliatorIds, $title, $message, $type, $data);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($notificationId, $userId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if ($notification && !$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
            
            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount($userId)
    {
        return Notification::where('user_id', $userId)
            ->unread()
            ->count();
    }

    /**
     * Get user notifications with pagination
     */
    public function getUserNotifications($userId, $perPage = 15, $type = null)
    {
        $query = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->where('type', $type);
        }

        return $query->paginate($perPage);
    }

    /**
     * Delete old notifications
     */
    public function deleteOldNotifications($days = 90)
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->delete();
    }

    /**
     * Send external notifications (WhatsApp, Email)
     */
    private function sendExternalNotifications($userId, $title, $message, $type)
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        // Check if WhatsApp notifications are enabled
        if (SystemSetting::getValue('whatsapp_notification', true) && $this->whatsappService) {
            try {
                $this->whatsappService->sendNotification($user->phone, $title, $message);
            } catch (\Exception $e) {
                Log::warning('Failed to send WhatsApp notification: ' . $e->getMessage());
            }
        }

        // Check if Email notifications are enabled
        if (SystemSetting::getValue('email_notification', true) && $this->emailService) {
            try {
                $this->emailService->sendNotification($user->email, $title, $message, $type);
            } catch (\Exception $e) {
                Log::warning('Failed to send Email notification: ' . $e->getMessage());
            }
        }
    }

    /**
     * Send lead verification notification
     */
    public function sendLeadVerificationNotification($leadId, $status, $notes = null)
    {
        $lead = \App\Models\Lead::find($leadId);
        if (!$lead) {
            return false;
        }

        $title = $status === 'verified' ? 'Lead Terverifikasi' : 'Lead Ditolak';
        $message = $status === 'verified' 
            ? "Lead {$lead->customer_name} telah diverifikasi"
            : "Lead {$lead->customer_name} ditolak" . ($notes ? ": {$notes}" : '');
        
        $type = $status === 'verified' ? 'success' : 'warning';

        return $this->createForUser(
            $lead->affiliatorProject->user_id,
            $title,
            $message,
            $type,
            ['lead_id' => $leadId, 'status' => $status]
        );
    }

    /**
     * Send commission notification
     */
    public function sendCommissionNotification($userId, $amount, $leadCustomerName, $type = 'earned')
    {
        $title = $type === 'earned' ? 'Komisi Baru!' : 'Komisi Ditarik';
        $action = $type === 'earned' ? 'mendapat' : 'menarik';
        
        $message = "Anda {$action} komisi Rp " . number_format($amount, 0, ',', '.');
        
        if ($type === 'earned' && $leadCustomerName) {
            $message .= " dari lead {$leadCustomerName}";
        }

        return $this->createForUser(
            $userId,
            $title,
            $message,
            $type === 'earned' ? 'success' : 'info',
            ['amount' => $amount, 'type' => $type]
        );
    }

    /**
     * Send withdrawal status notification
     */
    public function sendWithdrawalStatusNotification($withdrawalId, $status, $notes = null)
    {
        $withdrawal = \App\Models\CommissionWithdrawal::find($withdrawalId);
        if (!$withdrawal) {
            return false;
        }

        $statusMessages = [
            'approved' => 'Penarikan Disetujui',
            'rejected' => 'Penarikan Ditolak',
            'processed' => 'Penarikan Diproses'
        ];

        $title = $statusMessages[$status] ?? 'Update Penarikan';
        
        $message = "Penarikan komisi {$withdrawal->amount_formatted} ";
        
        switch ($status) {
            case 'approved':
                $message .= "telah disetujui";
                $type = 'success';
                break;
            case 'rejected':
                $message .= "ditolak" . ($notes ? ": {$notes}" : '');
                $type = 'error';
                break;
            case 'processed':
                $message .= "telah diproses dan dikirim ke rekening Anda";
                $type = 'success';
                break;
            default:
                $type = 'info';
        }

        return $this->createForUser(
            $withdrawal->user_id,
            $title,
            $message,
            $type,
            ['withdrawal_id' => $withdrawalId, 'status' => $status]
        );
    }

    /**
     * Send KTP verification notification
     */
    public function sendKtpVerificationNotification($affiliatorProjectId, $status, $notes = null)
    {
        $affiliatorProject = \App\Models\AffiliatorProject::find($affiliatorProjectId);
        if (!$affiliatorProject) {
            return false;
        }

        $title = $status === 'verified' ? 'KTP Terverifikasi' : 'KTP Ditolak';
        $message = $status === 'verified'
            ? "KTP Anda untuk project {$affiliatorProject->project->name} telah diverifikasi"
            : "KTP Anda untuk project {$affiliatorProject->project->name} ditolak" . ($notes ? ": {$notes}" : '');
        
        $type = $status === 'verified' ? 'success' : 'warning';

        return $this->createForUser(
            $affiliatorProject->user_id,
            $title,
            $message,
            $type,
            ['affiliator_project_id' => $affiliatorProjectId, 'status' => $status]
        );
    }

    /**
     * Send welcome notification for new user
     */
    public function sendWelcomeNotification($userId)
    {
        return $this->createForUser(
            $userId,
            'Selamat Datang!',
            'Akun Anda berhasil dibuat. Silakan lengkapi profil dan bergabung dengan project untuk mulai mendapatkan komisi.',
            'success',
            ['action' => 'welcome']
        );
    }

    /**
     * Send account status notification
     */
    public function sendAccountStatusNotification($userId, $status, $reason = null)
    {
        $title = $status === 'activated' ? 'Akun Diaktifkan' : 'Akun Dinonaktifkan';
        $message = $status === 'activated'
            ? 'Akun Anda telah diaktifkan kembali'
            : 'Akun Anda telah dinonaktifkan' . ($reason ? ": {$reason}" : '');
        
        $type = $status === 'activated' ? 'success' : 'warning';

        return $this->createForUser(
            $userId,
            $title,
            $message,
            $type,
            ['status' => $status, 'reason' => $reason]
        );
    }

    /**
     * Send project notification (new project, project updates, etc.)
     */
    public function sendProjectNotification($projectId, $title, $message, $type = 'info', $targetAudience = 'all')
    {
        $project = \App\Models\Project::find($projectId);
        if (!$project) {
            return false;
        }

        $userIds = [];

        switch ($targetAudience) {
            case 'admins':
                $userIds = $project->admins->pluck('id')->toArray();
                break;
            case 'affiliators':
                $userIds = \App\Models\AffiliatorProject::where('project_id', $projectId)
                    ->where('status', 'active')
                    ->pluck('user_id')
                    ->toArray();
                break;
            case 'all':
            default:
                // All project participants
                $adminIds = $project->admins->pluck('id')->toArray();
                $affiliatorIds = \App\Models\AffiliatorProject::where('project_id', $projectId)
                    ->pluck('user_id')
                    ->toArray();
                $userIds = array_unique(array_merge($adminIds, $affiliatorIds));
                break;
        }

        return $this->broadcast(
            $userIds,
            $title,
            $message,
            $type,
            ['project_id' => $projectId, 'audience' => $targetAudience]
        );
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats($userId = null, $days = 30)
    {
        $query = Notification::where('created_at', '>=', now()->subDays($days));
        
        if ($userId) {
            $query->where('user_id', $userId);
        }

        return [
            'total' => $query->count(),
            'unread' => $query->clone()->where('is_read', false)->count(),
            'by_type' => $query->clone()
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'recent' => $query->clone()
                ->where('created_at', '>=', now()->subDays(7))
                ->count()
        ];
    }
}