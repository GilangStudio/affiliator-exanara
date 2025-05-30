<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class ProjectAdminController extends Controller
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
     * Display admins for a specific project.
     */
    public function index(Project $project)
    {
        $project->load(['admins']);
        
        return view('pages.superadmin.projects.admins.index', compact('project'));
    }

    /**
     * Show the form for creating a new admin for the project.
     */
    public function create(Project $project)
    {
        return view('pages.superadmin.projects.admins.create', compact('project'));
    }

    /**
     * Store a newly created admin and assign to project.
     */
    public function store(Request $request, Project $project)
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

        DB::transaction(function () use ($request, $project) {
            $data = $request->only(['name', 'email', 'country_code', 'phone']);
            $data['password'] = Hash::make($request->password);
            $data['role'] = 'admin';
            $data['is_active'] = $request->boolean('is_active', true);

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $data['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
            }

            $admin = User::create($data);

            // Assign admin to project
            $project->admins()->attach($admin->id);

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'create_project_admin',
                "Admin baru dibuat dan ditugaskan ke project {$project->name}: {$admin->name} ({$admin->email})",
                $project->id,
                ['admin_id' => $admin->id]
            );

            // Send welcome notification to admin
            $this->notificationService->createForUser(
                $admin->id,
                'Selamat Datang!',
                "Akun admin Anda telah dibuat dan ditugaskan untuk mengelola project: {$project->name}",
                'success',
                ['project_id' => $project->id]
            );
        });

        return redirect()->route('superadmin.projects.admins.index', $project)
            ->with('success', 'Admin berhasil dibuat dan ditugaskan ke project!');
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Project $project, User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'User yang dipilih bukan admin!');
        }

        if (!$project->admins()->where('user_id', $admin->id)->exists()) {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditugaskan di project ini!');
        }

        $admin->load('adminProjects');

        return view('pages.superadmin.projects.admins.edit', compact('project', 'admin'));
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, Project $project, User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'User yang dipilih bukan admin!');
        }

        if (!$project->admins()->where('user_id', $admin->id)->exists()) {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditugaskan di project ini!');
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
            'update_project_admin',
            "Admin project {$project->name} diperbarui: {$admin->name}",
            $project->id,
            ['admin_id' => $admin->id, 'old_data' => $oldData, 'new_data' => $admin->only(['name', 'email', 'phone', 'is_active'])]
        );

        // Send notification if status changed
        if ($oldData['is_active'] !== $admin->is_active) {
            $this->notificationService->createForUser(
                $admin->id,
                'Status Akun Diperbarui',
                'Status akun Anda telah ' . ($admin->is_active ? 'diaktifkan' : 'dinonaktifkan'),
                $admin->is_active ? 'success' : 'warning'
            );
        }

        return redirect()->route('superadmin.projects.admins.index', $project)
            ->with('success', 'Admin berhasil diperbarui!');
    }

    /**
     * Remove admin from project and optionally delete the admin.
     */
    public function destroy(Project $project, User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'User yang dipilih bukan admin!');
        }

        if (!$project->admins()->where('user_id', $admin->id)->exists()) {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditugaskan di project ini!');
        }

        DB::transaction(function () use ($project, $admin) {
            // Remove from project
            $project->admins()->detach($admin->id);

            $adminName = $admin->name;
            $adminEmail = $admin->email;

            // Check if admin is assigned to other projects
            $otherProjectsCount = $admin->adminProjects()->count();

            if ($otherProjectsCount == 0) {
                // Delete admin entirely if not assigned to other projects
                if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                    Storage::disk('public')->delete($admin->profile_photo);
                }
                $admin->delete();

                $this->activityLogService->log(
                    Auth::id(),
                    'delete_project_admin',
                    "Admin {$adminName} dihapus dari project {$project->name} dan akun dihapus karena tidak ada project lain",
                    $project->id
                );
            } else {
                $this->activityLogService->log(
                    Auth::id(),
                    'remove_project_admin',
                    "Admin {$adminName} dihapus dari project {$project->name}",
                    $project->id
                );

                $this->notificationService->createForUser(
                    $admin->id,
                    'Project Assignment Removed',
                    "Anda telah dihapus dari assignment project: {$project->name}",
                    'warning',
                    ['project_id' => $project->id]
                );
            }
        });

        return redirect()->route('superadmin.projects.admins.index', $project)
            ->with('success', 'Admin berhasil dihapus dari project!');
    }

    /**
     * Toggle admin status.
     */
    public function toggleStatus(Project $project, User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'User yang dipilih bukan admin!');
        }

        if (!$project->admins()->where('user_id', $admin->id)->exists()) {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditugaskan di project ini!');
        }

        $newStatus = !$admin->is_active;
        $admin->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->activityLogService->log(
            Auth::id(),
            'toggle_project_admin_status',
            "Admin {$admin->name} {$statusText} untuk project {$project->name}",
            $project->id,
            ['admin_id' => $admin->id, 'new_status' => $newStatus]
        );

        // Send notification to admin
        $this->notificationService->createForUser(
            $admin->id,
            'Status Akun Diperbarui',
            "Akun Anda telah {$statusText} untuk project: {$project->name}",
            $newStatus ? 'success' : 'warning',
            ['project_id' => $project->id]
        );

        return back()->with('success', "Admin berhasil {$statusText}!");
    }

    /**
     * Reset admin password.
     */
    public function resetPassword(Request $request, Project $project, User $admin)
    {
        if ($admin->role !== 'admin') {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'User yang dipilih bukan admin!');
        }

        if (!$project->admins()->where('user_id', $admin->id)->exists()) {
            return redirect()->route('superadmin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditugaskan di project ini!');
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $admin->update([
            'password' => Hash::make($request->new_password)
        ]);

        $this->activityLogService->log(
            Auth::id(),
            'reset_project_admin_password',
            "Password admin {$admin->name} direset untuk project {$project->name}",
            $project->id,
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
}