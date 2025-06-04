<?php

namespace App\Services;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    protected $activityLogService;
    protected $notificationService;

    public function __construct(
        ActivityLogService $activityLogService,
        NotificationService $notificationService
    ) {
        $this->activityLogService = $activityLogService;
        $this->notificationService = $notificationService;
    }

    /**
     * Get total commission earned by user
     */
    public function getTotalCommissionEarned(User $user, $projectId = null)
    {
        $query = $user->commissionHistories()->where('type', 'earned');
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        return $query->sum('amount');
    }

    /**
     * Get total commission withdrawn by user
     */
    public function getTotalCommissionWithdrawn(User $user, $projectId = null)
    {
        $query = $user->commissionHistories()->where('type', 'withdrawn');
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }
        
        return $query->sum('amount');
    }

    /**
     * Get available commission balance
     */
    public function getAvailableCommission(User $user, $projectId = null)
    {
        $earned = $this->getTotalCommissionEarned($user, $projectId);
        $withdrawn = $this->getTotalCommissionWithdrawn($user, $projectId);
        
        return $earned - $withdrawn;
    }

    /**
     * Check if user can join more projects
     */
    public function canJoinMoreProjects(User $user)
    {
        $maxProjects = SystemSetting::getValue('max_projects_per_affiliator', 3);
        $currentProjects = $user->affiliatorProjects()->count();
        
        return $currentProjects < $maxProjects;
    }

    /**
     * Update user profile photo
     */
    public function updateProfilePhoto(User $user, $photoPath)
    {
        // Delete old photo if exists
        if ($user->profile_photo && Storage::exists('public/' . $user->profile_photo)) {
            Storage::delete('public/' . $user->profile_photo);
        }

        $user->update(['profile_photo' => $photoPath]);
        
        $this->activityLogService->log(
            $user->id,
            'update_profile_photo',
            'Foto profil diperbarui'
        );

        return $user;
    }

    /**
     * Delete user profile photo
     */
    public function deleteProfilePhoto(User $user)
    {
        if ($user->profile_photo && Storage::exists('public/' . $user->profile_photo)) {
            Storage::delete('public/' . $user->profile_photo);
            $user->update(['profile_photo' => null]);
            
            $this->activityLogService->log(
                $user->id,
                'delete_profile_photo',
                'Foto profil dihapus'
            );
        }

        return $user;
    }

    /**
     * Check if user is admin of specific project
     */
    public function isAdminOf(User $user, $projectId)
    {
        return $user->adminProjects()
            ->where('project_id', $projectId)
            ->exists();
    }

    /**
     * Create new user
     */
    public function createUser(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'country_code' => $data['country_code'], // Add this line
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'] ?? 'affiliator',
            'is_active' => $data['is_active'] ?? true
        ]);

        $this->activityLogService->log(
            $user->id,
            'register',
            'Registrasi akun baru'
        );

        // Send welcome notification
        $this->notificationService->createForUser(
            $user->id,
            'Selamat Datang!',
            'Akun Anda berhasil dibuat. Silakan lengkapi profil Anda.',
            'success'
        );

        return $user;
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data)
    {
        $oldData = $user->only(['name', 'email', 'phone']);
        $user->update($data);

        $this->activityLogService->log(
            $user->id,
            'update_profile',
            'Profil diperbarui',
            null,
            ['old_data' => $oldData, 'new_data' => $data]
        );

        return $user;
    }

    /**
     * Change user password
     */
    public function changePassword(User $user, $newPassword)
    {
        $user->update(['password' => Hash::make($newPassword)]);

        $this->activityLogService->log(
            $user->id,
            'change_password',
            'Password diubah'
        );

        $this->notificationService->createForUser(
            $user->id,
            'Password Diubah',
            'Password akun Anda berhasil diubah',
            'info'
        );

        return $user;
    }

    /**
     * Activate user account
     */
    public function activateUser(User $user, $adminId = null)
    {
        $user->update(['is_active' => true]);

        $adminId = $adminId ?: Auth::id();
        
        $this->activityLogService->log(
            $adminId,
            'activate_user',
            "User {$user->name} diaktifkan"
        );

        $this->notificationService->createForUser(
            $user->id,
            'Akun Diaktifkan',
            'Akun Anda telah diaktifkan kembali',
            'success'
        );

        return $user;
    }

    /**
     * Deactivate user account
     */
    public function deactivateUser(User $user, $reason = null, $adminId = null)
    {
        $user->update(['is_active' => false]);

        $adminId = $adminId ?: Auth::id();
        
        $this->activityLogService->log(
            $adminId,
            'deactivate_user',
            "User {$user->name} dinonaktifkan" . ($reason ? ": {$reason}" : ''),
            null,
            ['reason' => $reason]
        );

        $this->notificationService->createForUser(
            $user->id,
            'Akun Dinonaktifkan',
            'Akun Anda telah dinonaktifkan' . ($reason ? ": {$reason}" : ''),
            'warning'
        );

        return $user;
    }

    /**
     * Get user dashboard statistics
     */
    public function getUserDashboardStats(User $user)
    {
        if (!$user->is_affiliator) {
            return [];
        }

        $stats = [
            'total_projects' => $user->affiliatorProjects()->count(),
            'active_projects' => $user->affiliatorProjects()->where('status', 'active')->count(),
            'total_leads' => 0,
            'verified_leads' => 0,
            'total_commission_earned' => $this->getTotalCommissionEarned($user),
            'total_commission_withdrawn' => $this->getTotalCommissionWithdrawn($user),
            'available_commission' => $this->getAvailableCommission($user),
            'pending_withdrawals' => $user->commissionWithdrawals()->pending()->count()
        ];

        // Calculate leads statistics
        foreach ($user->affiliatorProjects as $affiliatorProject) {
            $stats['total_leads'] += $affiliatorProject->leads()->count();
            $stats['verified_leads'] += $affiliatorProject->leads()->verified()->count();
        }

        return $stats;
    }

    /**
     * Get user activity summary
     */
    public function getUserActivitySummary(User $user, $days = 30)
    {
        return $this->activityLogService->getUserActivitySummary($user->id, $days);
    }
}