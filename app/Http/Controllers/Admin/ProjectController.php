<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectAdmin;
use Illuminate\Http\Request;
use App\Models\CRM\ProjectCRM;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
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
     * Display a listing of projects that the admin manages.
     */
    public function index(Request $request)
    {
        // Get projects that current user is admin of
        $query = User::find(Auth::user()->id)->adminProjects();

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('projects.is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('projects.is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('projects.name', 'like', "%{$search}%")
                  ->orWhere('projects.slug', 'like', "%{$search}%")
                  ->orWhere('projects.description', 'like', "%{$search}%");
            });
        }

        $query = $query->with(['projectCrm:id,nama_project'])->withCount('units');

        // Sort
        $sortBy = $request->get('sort', 'projects.created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $projects = $query->paginate(15);

        return view('pages.admin.projects.index', compact('projects'));
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        // Check if current user is admin of this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $project->id)->exists()) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat project ini.');
        }

        $project->load([
            'admins',
            'affiliatorProjects.user',
            'leads',
            'units'
        ]);

        $stats = [
            'total_affiliators' => $project->affiliatorProjects()->count(),
            'active_affiliators' => $project->affiliatorProjects()->where('status', 'active')->count(),
            'total_leads' => $project->leads()->count(),
            'verified_leads' => $project->leads()->verified()->count(),
            'total_units' => $project->units()->count(),
            'active_units' => $project->units()->active()->count(),
        ];

        $recentLeads = $project->leads()
            ->with(['affiliatorProject.user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.admin.projects.show', compact('project', 'stats', 'recentLeads'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        // Check if current user is admin of this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $project->id)->exists()) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit project ini.');
        }

        return view('pages.admin.projects.edit', compact('project'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        // Check if current user is admin of this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $project->id)->exists()) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit project ini.');
        }

        $request->validate([
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'terms_and_conditions' => 'required|string',
            'additional_info' => 'nullable|string',
            'require_digital_signature' => 'boolean',
            'is_active' => 'boolean'
        ]);

        DB::transaction(function () use ($request, $project) {
            $oldData = $project->only(['location', 'is_active']);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($project->logo && Storage::disk('public')->exists($project->logo)) {
                    Storage::disk('public')->delete($project->logo);
                }
                $project->logo = $request->file('logo')->store('projects/logos', 'public');
            }

            // Update project (admin can't change name and CRM connection)
            $project->update([
                'location' => $request->location,
                'description' => $request->description,
                'terms_and_conditions' => $request->terms_and_conditions,
                'additional_info' => $request->additional_info,
                'require_digital_signature' => $request->require_digital_signature == '1' ? true : false,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'update_project',
                "Project diperbarui oleh admin: {$project->name}",
                $project->id,
                ['old_data' => $oldData, 'new_data' => $project->only(['location', 'is_active'])]
            );
        });

        return redirect()->route('admin.projects.index')
            ->with('success', 'Project berhasil diperbarui!');
    }

    /**
     * Toggle project status.
     */
    public function toggleStatus(Project $project)
    {
        // Check if current user is admin of this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $project->id)->exists()) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengubah status project ini.');
        }

        $newStatus = !$project->is_active;
        $project->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->activityLogService->log(
            Auth::id(),
            'toggle_project_status',
            "Project {$project->name} {$statusText} oleh admin",
            $project->id
        );

        // Notify other project admins
        $adminIds = $project->admins()->where('users.id', '!=', Auth::id())->pluck('users.id')->toArray();
        if (!empty($adminIds)) {
            $this->notificationService->broadcast(
                $adminIds,
                "Project Status Update",
                "Project {$project->name} telah {$statusText} oleh " . Auth::user()->name,
                $newStatus ? 'success' : 'warning'
            );
        }

        return back()->with('success', "Project berhasil {$statusText}!");
    }

    /**
     * Get project statistics
     */
    public function statistics(Project $project)
    {
        // Check if current user is admin of this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $project->id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $stats = [
            'total_affiliators' => $project->affiliatorProjects()->count(),
            'active_affiliators' => $project->affiliatorProjects()->where('status', 'active')->count(),
            'pending_affiliators' => $project->affiliatorProjects()->where('verification_status', 'pending')->count(),
            'total_leads' => $project->leads()->count(),
            'verified_leads' => $project->leads()->verified()->count(),
            'pending_leads' => $project->leads()->where('verification_status', 'pending')->count(),
            'total_units' => $project->units()->count(),
            'active_units' => $project->units()->active()->count(),
            'total_commission_earned' => $project->commissionHistories()->where('type', 'earned')->sum('amount'),
            'total_commission_withdrawn' => $project->commissionHistories()->where('type', 'withdrawn')->sum('amount')
        ];

        return response()->json($stats);
    }
}