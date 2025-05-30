<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

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
        $availableAdmins = User::admins()->active()
            ->whereNotIn('id', $project->admins->pluck('id'))
            ->get();

        return view('pages.superadmin.projects.admins.index', compact('project', 'availableAdmins'));
    }

    /**
     * Add admin to project.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'admin_id' => 'required|exists:users,id'
        ]);

        $admin = User::find($request->admin_id);

        if ($admin->role !== 'admin') {
            return back()->with('error', 'User yang dipilih bukan admin!');
        }

        if ($project->admins()->where('user_id', $admin->id)->exists()) {
            return back()->with('error', 'Admin sudah ditugaskan di project ini!');
        }

        $project->admins()->attach($admin->id);

        $this->activityLogService->log(
            Auth::id(),
            'add_project_admin',
            "Admin {$admin->name} ditambahkan ke project {$project->name}",
            $project->id
        );

        $this->notificationService->createForUser(
            $admin->id,
            'Project Assignment',
            "Anda telah ditugaskan sebagai admin untuk project: {$project->name}",
            'info',
            ['project_id' => $project->id]
        );

        return back()->with('success', 'Admin berhasil ditambahkan ke project!');
    }

    /**
     * Remove admin from project.
     */
    public function destroy(Project $project, User $admin)
    {
        if (!$project->admins()->where('user_id', $admin->id)->exists()) {
            return back()->with('error', 'Admin tidak ditemukan di project ini!');
        }

        $project->admins()->detach($admin->id);

        $this->activityLogService->log(
            Auth::id(),
            'remove_project_admin',
            "Admin {$admin->name} dihapus dari project {$project->name}",
            $project->id
        );

        $this->notificationService->createForUser(
            $admin->id,
            'Project Assignment Removed',
            "Anda telah dihapus dari assignment project: {$project->name}",
            'warning',
            ['project_id' => $project->id]
        );

        return back()->with('success', 'Admin berhasil dihapus dari project!');
    }
}