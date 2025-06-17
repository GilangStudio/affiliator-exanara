<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectAdmin;
use Illuminate\Http\Request;
use App\Services\GeneralService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
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
        // $project->load('admins');

        $admins = $project->admins()->where('users.id', '!=', $project->pic_user_id)->get();

        return view('pages.admin.projects.admins.index', compact('project', 'admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create(Project $project)
    {
        // Check if current user can edit this project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Hanya admin PIC yang dapat menambah admin baru.');
        }

        return view('pages.admin.projects.admins.create', compact('project'));
    }

    /**
     * Store a newly created admin.
     */
    public function store(Request $request, Project $project)
    {
        // Check if current user can edit this project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Hanya admin PIC yang dapat menambah admin baru.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:6|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama harus diisi',
            'username.required' => 'Username harus diisi',
            'username.unique' => 'Username sudah digunakan',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah digunakan',
            'phone.required' => 'Nomor telepon harus diisi',
            'password.required' => 'Password harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $admin = DB::transaction(function () use ($request, $project) {
            $data = [
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'country_code' => '+62',
                'phone' => GeneralService::formatPhoneNumber($request->phone),
                'password' => bcrypt($request->password),
                'role' => 'admin',
                'is_active' => $request->boolean('is_active', true),
                'is_pic' => false,
            ];

            // Handle profile photo upload
            if ($request->hasFile('profile_photo')) {
                $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
            }

            $admin = User::create($data);

            // Assign admin to project
            $project->admins()->attach($admin->id);

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'create_admin',
                "Admin baru dibuat untuk project {$project->name}: {$admin->name}",
                $project->id,
                ['admin_id' => $admin->id]
            );

            // Send notification to new admin
            $this->notificationService->createForUser(
                $admin->id,
                'Akun Admin Dibuat',
                "Anda telah ditugaskan sebagai admin untuk project '{$project->name}'.",
                'info',
                ['project_id' => $project->id]
            );

            return $admin;
        });

        return redirect()->route('admin.projects.admins.index', $project)
            ->with('success', 'Admin berhasil dibuat dan ditugaskan ke project!');
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(Project $project, User $admin)
    {
        // Check if current user can edit this project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Hanya admin PIC yang dapat mengedit admin.');
        }

        // Check if admin is assigned to this project
        if (!$project->admins()->where('users.id', $admin->id)->exists()) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditemukan pada project ini!');
        }

        // Prevent editing PIC user
        if ($admin->id == $project->pic_user_id) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Tidak dapat mengedit admin PIC melalui menu ini. Gunakan halaman Profil & Pengaturan.');
        }

        $admin->load('adminProjects');

        return view('pages.admin.projects.admins.edit', compact('project', 'admin'));
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, Project $project, User $admin)
    {
        // Check if current user can edit this project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Hanya admin PIC yang dapat mengedit admin.');
        }

        // Check if admin is assigned to this project
        if (!$project->admins()->where('users.id', $admin->id)->exists()) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditemukan pada project ini!');
        }

        // Prevent editing PIC user
        if ($admin->id == $project->pic_user_id) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Tidak dapat mengedit admin PIC melalui menu ini. Gunakan halaman edit project.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:6|max:255|unique:users,username,' . $admin->id,
            'email' => 'required|email|max:255|unique:users,email,' . $admin->id,
            'phone' => 'required|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama harus diisi',
            'username.required' => 'Username harus diisi',
            'username.unique' => 'Username sudah digunakan',
            'email.required' => 'Email harus diisi',
            'email.unique' => 'Email sudah digunakan',
            'phone.required' => 'Nomor telepon harus diisi',
            'password.min' => 'Password minimal 8 karakter',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $oldData = $admin->only(['name', 'email', 'is_active']);

        $data = [
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => GeneralService::formatPhoneNumber($request->phone),
            'is_active' => $request->boolean('is_active', true),
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                Storage::disk('public')->delete($admin->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        $admin->update($data);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'update_admin',
            "Admin diperbarui untuk project {$project->name}: {$admin->name}",
            $project->id,
            ['admin_id' => $admin->id, 'old_data' => $oldData, 'new_data' => $admin->only(['name', 'email', 'is_active'])]
        );

        return redirect()->route('admin.projects.admins.index', $project)
            ->with('success', 'Admin berhasil diperbarui!');
    }

    /**
     * Remove the specified admin from project.
     */
    public function destroy(Project $project, User $admin)
    {
        // Check if current user can edit this project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Hanya admin PIC yang dapat menghapus admin.');
        }

        // Check if admin is assigned to this project
        if (!$project->admins()->where('users.id', $admin->id)->exists()) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditemukan pada project ini!');
        }

        // Prevent removing PIC user
        if ($admin->id == $project->pic_user_id) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Tidak dapat menghapus admin PIC dari project.');
        }

        // Prevent removing self
        if ($admin->id == Auth::id()) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Anda tidak dapat menghapus diri sendiri dari project.');
        }

        $adminName = $admin->name;

        // Remove admin from project
        $project->admins()->detach($admin->id);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'remove_admin',
            "Admin dihapus dari project {$project->name}: {$adminName}",
            $project->id,
            ['admin_id' => $admin->id]
        );

        // Send notification to removed admin
        $this->notificationService->createForUser(
            $admin->id,
            'Dihapus dari Project',
            "Anda telah dihapus dari project '{$project->name}'.",
            'warning',
            ['project_id' => $project->id]
        );

        return redirect()->route('admin.projects.admins.index', $project)
            ->with('success', 'Admin berhasil dihapus dari project!');
    }

    /**
     * Toggle admin status.
     */
    public function toggleStatus(Project $project, User $admin)
    {
        // Check if current user can edit this project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Hanya admin PIC yang dapat mengubah status admin.');
        }

        // Check if admin is assigned to this project
        if (!$project->admins()->where('users.id', $admin->id)->exists()) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Admin tidak ditemukan pada project ini!');
        }

        // Prevent changing PIC status
        if ($admin->id == $project->pic_user_id) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Tidak dapat mengubah status admin PIC.');
        }

        // Prevent changing own status
        if ($admin->id == Auth::id()) {
            return redirect()->route('admin.projects.admins.index', $project)
                ->with('error', 'Anda tidak dapat mengubah status diri sendiri.');
        }

        $newStatus = !$admin->is_active;
        $admin->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->activityLogService->log(
            Auth::id(),
            'toggle_admin_status',
            "Admin {$admin->name} {$statusText} untuk project {$project->name}",
            $project->id,
            ['admin_id' => $admin->id, 'new_status' => $newStatus]
        );

        return back()->with('success', "Admin berhasil {$statusText}!");
    }

    /**
     * Reset admin password.
     */
    public function resetPassword(Request $request, Project $project, User $admin)
    {
        // Check if current user can edit this project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if admin is assigned to this project
        if (!$project->admins()->where('users.id', $admin->id)->exists()) {
            return response()->json(['error' => 'Admin tidak ditemukan pada project ini!'], 404);
        }

        // Prevent resetting PIC password
        if ($admin->id == $project->pic_user_id) {
            return response()->json(['error' => 'Tidak dapat reset password admin PIC.'], 403);
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ], [
            'new_password.required' => 'Password baru harus diisi',
            'new_password.min' => 'Password minimal 8 karakter',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $admin->update([
            'password' => bcrypt($request->new_password)
        ]);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'reset_admin_password',
            "Password admin direset untuk project {$project->name}: {$admin->name}",
            $project->id,
            ['admin_id' => $admin->id]
        );

        // Send notification to admin
        $this->notificationService->createForUser(
            $admin->id,
            'Password Direset',
            "Password Anda telah direset oleh admin PIC project '{$project->name}'. Silakan login dengan password baru.",
            'warning',
            ['project_id' => $project->id]
        );

        return back()->with('success', 'Password admin berhasil direset!');
    }
}