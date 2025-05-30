<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\UserService;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
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
     * Display a listing of admins.
     */
    public function index(Request $request)
    {
        $query = User::admins();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $admins = $query->paginate(15);

        // Load project counts for each admin
        $admins->each(function($admin) {
            $admin->projects_count = $admin->adminProjects()->count();
        });

        return view('pages.superadmin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        return view('pages.superadmin.admins.create');
    }

    /**
     * Store a newly created admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $data = $request->only(['name', 'email', 'country_code', 'phone']);
        $data['password'] = Hash::make($request->password);
        $data['role'] = 'admin';
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        $admin = User::create($data);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'create_admin',
            "Admin baru dibuat: {$admin->name} ({$admin->email})",
            null,
            ['admin_id' => $admin->id]
        );

        // Send welcome notification to admin
        $this->notificationService->createForUser(
            $admin->id,
            'Selamat Datang!',
            'Akun admin Anda telah dibuat. Silakan login untuk mengakses sistem.',
            'success'
        );

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Admin berhasil dibuat!');
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'User yang dipilih bukan admin!');
        }

        $admin->load('adminProjects.project');

        return view('pages.superadmin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'User yang dipilih bukan admin!');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
            'country_code' => 'required|string|max:5',
            'phone' => 'required|string|max:20|unique:users,phone,' . $admin->id,
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);

        $oldData = $admin->only(['name', 'email', 'phone', 'is_active']);
        
        $data = $request->only(['name', 'email', 'country_code', 'phone']);
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle password update
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                Storage::disk('public')->delete($admin->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
        }

        $admin->update($data);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'update_admin',
            "Admin diperbarui: {$admin->name}",
            null,
            ['admin_id' => $admin->id, 'old_data' => $oldData, 'new_data' => $admin->only(['name', 'email', 'phone', 'is_active'])]
        );

        // Send notification if status changed
        if ($oldData['is_active'] !== $admin->is_active) {
            $this->notificationService->sendAccountStatusNotification(
                $admin->id,
                $admin->is_active ? 'activated' : 'deactivated'
            );
        }

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Admin berhasil diperbarui!');
    }

    /**
     * Remove the specified admin.
     */
    public function destroy(User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'User yang dipilih bukan admin!');
        }

        // Check if admin has active projects
        $projectsCount = $admin->adminProjects()->count();
        if ($projectsCount > 0) {
            return redirect()->route('superadmin.admins.index')
                ->with('error', "Admin tidak dapat dihapus karena masih mengelola {$projectsCount} project!");
        }

        $adminName = $admin->name;
        $adminEmail = $admin->email;

        // Delete profile photo
        if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
            Storage::disk('public')->delete($admin->profile_photo);
        }

        // Delete admin
        $admin->delete();

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'delete_admin',
            "Admin dihapus: {$adminName} ({$adminEmail})"
        );

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Admin berhasil dihapus!');
    }

    /**
     * Toggle admin status.
     */
    public function toggleStatus(User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'User yang dipilih bukan admin!');
        }

        $newStatus = !$admin->is_active;
        $admin->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->activityLogService->log(
            Auth::id(),
            'toggle_admin_status',
            "Admin {$admin->name} {$statusText}",
            null,
            ['admin_id' => $admin->id, 'new_status' => $newStatus]
        );

        // Send notification to admin
        $this->notificationService->sendAccountStatusNotification(
            $admin->id,
            $newStatus ? 'activated' : 'deactivated'
        );

        return back()->with('success', "Admin berhasil {$statusText}!");
    }

    /**
     * Reset admin password.
     */
    public function resetPassword(Request $request, User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'User yang dipilih bukan admin!');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $admin->update([
            'password' => Hash::make($request->new_password)
        ]);

        $this->activityLogService->log(
            Auth::id(),
            'reset_admin_password',
            "Password admin {$admin->name} direset",
            null,
            ['admin_id' => $admin->id]
        );

        // Send notification to admin
        $this->notificationService->createForUser(
            $admin->id,
            'Password Direset',
            'Password akun Anda telah direset oleh Super Admin. Silakan login dengan password baru.',
            'warning'
        );

        return back()->with('success', 'Password admin berhasil direset!');
    }

    /**
     * Remove admin profile photo.
     */
    public function removePhoto(User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.admins.index')
                ->with('error', 'User yang dipilih bukan admin!');
        }

        if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
            Storage::disk('public')->delete($admin->profile_photo);
            $admin->update(['profile_photo' => null]);

            $this->activityLogService->log(
                Auth::id(),
                'remove_admin_photo',
                "Foto profil admin {$admin->name} dihapus",
                null,
                ['admin_id' => $admin->id]
            );

            return back()->with('success', 'Foto profil berhasil dihapus!');
        }

        return back()->with('error', 'Tidak ada foto profil untuk dihapus!');
    }
}