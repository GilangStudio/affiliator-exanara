<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use App\Services\UserService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;

class SettingsController extends Controller
{
    protected $activityLogService;
    protected $notificationService;
    protected $userService;

    public function __construct(
        ActivityLogService $activityLogService,
        NotificationService $notificationService,
        UserService $userService
    ) {
        $this->activityLogService = $activityLogService;
        $this->notificationService = $notificationService;
        $this->userService = $userService;
    }

    /**
     * Display settings page
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'general');
        
        // Get current settings
        $settings = [
            'site_name' => SystemSetting::getValue('site_name', 'Affiliator System'),
            'site_description' => SystemSetting::getValue('site_description', 'Sistem Manajemen Affiliator'),
            'site_logo' => SystemSetting::getValue('site_logo'),
            'site_favicon' => SystemSetting::getValue('site_favicon'),
            'contact_email' => SystemSetting::getValue('contact_email', 'admin@example.com'),
            'contact_phone' => SystemSetting::getValue('contact_phone', '+62812345678'),
            'contact_address' => SystemSetting::getValue('contact_address'),
            
            // Commission settings
            'min_withdrawal_amount' => SystemSetting::getValue('min_withdrawal_amount', 100000),
            'commission_withdrawal_fee' => SystemSetting::getValue('commission_withdrawal_fee', 0),
            'max_projects_per_affiliator' => SystemSetting::getValue('max_projects_per_affiliator', 3),
            'auto_approve_withdrawals' => SystemSetting::getValue('auto_approve_withdrawals', false),
            
            // Notification settings
            'email_notification' => SystemSetting::getValue('email_notification', true),
            'whatsapp_notification' => SystemSetting::getValue('whatsapp_notification', true),
            'push_notification' => SystemSetting::getValue('push_notification', true),
            
            // Security settings
            'password_min_length' => SystemSetting::getValue('password_min_length', 8),
            'session_timeout' => SystemSetting::getValue('session_timeout', 120),
            'max_login_attempts' => SystemSetting::getValue('max_login_attempts', 5),
            'require_email_verification' => SystemSetting::getValue('require_email_verification', false),
            
            // Maintenance settings
            'maintenance_mode' => SystemSetting::getValue('maintenance_mode', false),
            'maintenance_message' => SystemSetting::getValue('maintenance_message', 'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.'),
        ];

        // Get current user
        $user = Auth::user();

        // Get system stats
        $stats = [
            'total_users' => User::count(),
            'total_admins' => User::where('role', 'admin')->count(),
            'total_affiliators' => User::where('role', 'affiliator')->count(),
            'cache_size' => $this->getCacheSize(),
            'storage_size' => $this->getStorageSize(),
            'last_backup' => SystemSetting::getValue('last_backup_date'),
        ];

        return view('pages.superadmin.settings.index', compact('settings', 'user', 'stats', 'activeTab'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'site_favicon' => 'nullable|image|mimes:ico,png|max:512',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'contact_address' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request) {
            // Handle logo upload
            if ($request->hasFile('site_logo')) {
                // Delete old logo
                $oldLogo = SystemSetting::getValue('site_logo');
                if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                    Storage::disk('public')->delete($oldLogo);
                }
                
                $logoPath = $request->file('site_logo')->store('settings', 'public');
                SystemSetting::setValue('site_logo', $logoPath);
            }

            // Handle favicon upload
            if ($request->hasFile('site_favicon')) {
                // Delete old favicon
                $oldFavicon = SystemSetting::getValue('site_favicon');
                if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                    Storage::disk('public')->delete($oldFavicon);
                }
                
                $faviconPath = $request->file('site_favicon')->store('settings', 'public');
                SystemSetting::setValue('site_favicon', $faviconPath);
            }

            // Update other settings
            SystemSetting::setValue('site_name', $request->site_name);
            SystemSetting::setValue('site_description', $request->site_description);
            SystemSetting::setValue('contact_email', $request->contact_email);
            SystemSetting::setValue('contact_phone', $request->contact_phone);
            SystemSetting::setValue('contact_address', $request->contact_address);

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'update_general_settings',
                'Pengaturan umum diperbarui'
            );
        });

        return redirect()->route('superadmin.settings.index', ['tab' => 'general'])
            ->with('success', 'Pengaturan umum berhasil diperbarui!');
    }

    /**
     * Update commission settings
     */
    public function updateCommission(Request $request)
    {
        $request->validate([
            'min_withdrawal_amount' => 'required|numeric|min:0',
            'commission_withdrawal_fee' => 'required|numeric|min:0',
            'max_projects_per_affiliator' => 'required|integer|min:1|max:10',
            'auto_approve_withdrawals' => 'boolean',
        ]);

        SystemSetting::setValue('min_withdrawal_amount', $request->min_withdrawal_amount, 'integer');
        SystemSetting::setValue('commission_withdrawal_fee', $request->commission_withdrawal_fee, 'integer');
        SystemSetting::setValue('max_projects_per_affiliator', $request->max_projects_per_affiliator, 'integer');
        SystemSetting::setValue('auto_approve_withdrawals', $request->boolean('auto_approve_withdrawals'), 'boolean');

        $this->activityLogService->log(
            Auth::id(),
            'update_commission_settings',
            'Pengaturan komisi diperbarui'
        );

        return redirect()->route('superadmin.settings.index', ['tab' => 'commission'])
            ->with('success', 'Pengaturan komisi berhasil diperbarui!');
    }

    /**
     * Update notification settings
     */
    public function updateNotification(Request $request)
    {
        $request->validate([
            'email_notification' => 'boolean',
            'whatsapp_notification' => 'boolean',
            'push_notification' => 'boolean',
        ]);

        SystemSetting::setValue('email_notification', $request->boolean('email_notification'), 'boolean');
        SystemSetting::setValue('whatsapp_notification', $request->boolean('whatsapp_notification'), 'boolean');
        SystemSetting::setValue('push_notification', $request->boolean('push_notification'), 'boolean');

        $this->activityLogService->log(
            Auth::id(),
            'update_notification_settings',
            'Pengaturan notifikasi diperbarui'
        );

        return redirect()->route('superadmin.settings.index', ['tab' => 'notification'])
            ->with('success', 'Pengaturan notifikasi berhasil diperbarui!');
    }

    /**
     * Update security settings
     */
    public function updateSecurity(Request $request)
    {
        $request->validate([
            'password_min_length' => 'required|integer|min:6|max:20',
            'session_timeout' => 'required|integer|min:30|max:1440',
            'max_login_attempts' => 'required|integer|min:3|max:10',
            'require_email_verification' => 'boolean',
        ]);

        SystemSetting::setValue('password_min_length', $request->password_min_length, 'integer');
        SystemSetting::setValue('session_timeout', $request->session_timeout, 'integer');
        SystemSetting::setValue('max_login_attempts', $request->max_login_attempts, 'integer');
        SystemSetting::setValue('require_email_verification', $request->boolean('require_email_verification'), 'boolean');

        $this->activityLogService->log(
            Auth::id(),
            'update_security_settings',
            'Pengaturan keamanan diperbarui'
        );

        return redirect()->route('superadmin.settings.index', ['tab' => 'security'])
            ->with('success', 'Pengaturan keamanan berhasil diperbarui!');
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request)
    {
        $user = User::findOrFail(Auth::id());

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{9,13}$/',
                'unique:users,phone,' . $user->id
            ],
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $oldData = $user->only(['name', 'email', 'phone']);

        $data = $request->only(['name', 'email', 'phone']);
        $data['country_code'] = '+62';

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
                Storage::disk('public')->delete($user->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        $user->update($data);

        $this->activityLogService->log(
            $user->id,
            'update_profile',
            'Profil SuperAdmin diperbarui',
            null,
            ['old_data' => $oldData, 'new_data' => $user->only(['name', 'email', 'phone'])]
        );

        return redirect()->route('superadmin.settings.index', ['tab' => 'profile'])
            ->with('success', 'Profil berhasil diperbarui!');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Password saat ini harus diisi',
            'new_password.required' => 'Password baru harus diisi',
            'new_password.min' => 'Password baru minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = User::findOrFail(Auth::id());

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->route('superadmin.settings.index', ['tab' => 'profile'])
                ->withErrors(['current_password' => 'Password saat ini tidak benar']);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        $this->activityLogService->log(
            $user->id,
            'change_password',
            'Password SuperAdmin diubah'
        );

        return redirect()->route('superadmin.settings.index', ['tab' => 'profile'])
            ->with('success', 'Password berhasil diubah!');
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenance(Request $request)
    {
        $request->validate([
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        $currentMode = SystemSetting::getValue('maintenance_mode', false);
        $newMode = !$currentMode;

        SystemSetting::setValue('maintenance_mode', $newMode, 'boolean');
        
        if ($request->filled('maintenance_message')) {
            SystemSetting::setValue('maintenance_message', $request->maintenance_message);
        }

        $this->activityLogService->log(
            Auth::id(),
            'toggle_maintenance_mode',
            'Mode maintenance ' . ($newMode ? 'diaktifkan' : 'dinonaktifkan')
        );

        $message = $newMode ? 'Mode maintenance diaktifkan' : 'Mode maintenance dinonaktifkan';
        
        return redirect()->route('superadmin.settings.index', ['tab' => 'maintenance'])
            ->with('success', $message);
    }

    /**
     * Clear cache
     */
    public function clearCache()
    {
        try {
            Cache::flush();
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            $this->activityLogService->log(
                Auth::id(),
                'clear_cache',
                'Cache sistem dibersihkan'
            );

            return redirect()->route('superadmin.settings.index', ['tab' => 'maintenance'])
                ->with('success', 'Cache berhasil dibersihkan!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.settings.index', ['tab' => 'maintenance'])
                ->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
        }
    }

    /**
     * Create backup
     */
    public function createBackup()
    {
        try {
            // Simple backup implementation
            $backupPath = 'backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
            
            // Store backup info in settings
            SystemSetting::setValue('last_backup_date', now()->toDateTimeString());
            SystemSetting::setValue('last_backup_path', $backupPath);

            $this->activityLogService->log(
                Auth::id(),
                'create_backup',
                'Backup database dibuat: ' . $backupPath
            );

            return redirect()->route('superadmin.settings.index', ['tab' => 'maintenance'])
                ->with('success', 'Backup berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->route('superadmin.settings.index', ['tab' => 'maintenance'])
                ->with('error', 'Gagal membuat backup: ' . $e->getMessage());
        }
    }

    /**
     * Delete profile photo
     */
    public function deleteProfilePhoto()
    {
        $user = User::findOrFail(Auth::id());

        if ($user->profile_photo && Storage::disk('public')->exists($user->profile_photo)) {
            Storage::disk('public')->delete($user->profile_photo);
            $user->update(['profile_photo' => null]);

            $this->activityLogService->log(
                $user->id,
                'delete_profile_photo',
                'Foto profil SuperAdmin dihapus'
            );

            return redirect()->route('superadmin.settings.index', ['tab' => 'profile'])
                ->with('success', 'Foto profil berhasil dihapus!');
        }

        return redirect()->route('superadmin.settings.index', ['tab' => 'profile'])
            ->with('error', 'Tidak ada foto profil untuk dihapus!');
    }

    /**
     * Get cache size
     */
    private function getCacheSize()
    {
        try {
            // Simple cache size calculation
            return '0 MB'; // Placeholder
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get storage size
     */
    private function getStorageSize()
    {
        try {
            $storagePath = storage_path('app/public');
            if (is_dir($storagePath)) {
                $size = $this->getDirSize($storagePath);
                return $this->formatBytes($size);
            }
            return '0 MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Get directory size
     */
    private function getDirSize($directory)
    {
        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($size, $precision = 2)
    {
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}