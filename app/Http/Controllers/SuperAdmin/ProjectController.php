<?php

namespace App\Http\Controllers\SuperAdmin;

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
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $query = Project::query();

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
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $query = $query->with(['projectCrm:id,nama_project'])->withCount('units');

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $projects = $query->paginate(15);

        return view('pages.superadmin.projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        return view('pages.superadmin.projects.create');
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_source' => 'required|in:new,existing',
            // 'existing_project_id' => 'nullable|required_if:project_source,existing|exists:project,id',
            'name' => 'nullable|required_if:project_source,new|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'terms_and_conditions' => 'required|string',
            'additional_info' => 'nullable|string',
            'require_digital_signature' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($request->project_source === 'existing') {
            $existingProject = Project::where('crm_project_id', $request->existing_project_id)->first();
            if ($existingProject) {
                return redirect()->back()->withErrors(['existing_project_id' => 'Project ini sudah terdaftar.']);
            }
        }

        DB::transaction(function () use ($request) {
            // Handle logo upload
            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('projects/logos', 'public');
            }

            // Determine project name and description
            if ($request->project_source === 'existing') {
                $crmProject = ProjectCRM::findOrFail($request->existing_project_id);
                $projectName = $crmProject->nama_project;
            } else {
                $projectName = $request->name;
            }

            // Create project
            $project = Project::create([
                'name' => $projectName,
                'location' => $request->location,
                'logo' => $logoPath,
                'description' => $request->description,
                'terms_and_conditions' => $request->terms_and_conditions,
                'additional_info' => $request->additional_info,
                'require_digital_signature' => $request->boolean('require_digital_signature', true),
                'is_active' => $request->boolean('is_active', true),
                'crm_project_id' => $request->project_source === 'existing' ? $request->existing_project_id : null,
            ]);

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'create_project',
                "Project baru dibuat: {$project->name}",
                $project->id
            );
        });

        return redirect()->route('superadmin.projects.index')
            ->with('success', 'Project berhasil dibuat!');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
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
            // 'total_commission_earned' => $project->commissionHistories()->where('type', 'earned')->sum('amount'),
            // 'total_commission_withdrawn' => $project->commissionHistories()->where('type', 'withdrawn')->sum('amount')
        ];

        $recentLeads = $project->leads()
            ->with(['affiliatorProject.user'])
            ->latest()
            ->limit(10)
            ->get();

        return view('pages.superadmin.projects.show', compact('project', 'stats', 'recentLeads'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        $admins = User::admins()->active()->whereNotIn('id', $project->admins->pluck('id'))->get();

        return view('pages.superadmin.projects.edit', compact('project', 'admins'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'project_source' => 'required|in:new,existing',
            // 'existing_project_id' => 'nullable|required_if:project_source,existing|exists:project_crms,id',
            'name' => 'nullable|required_if:project_source,new|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'terms_and_conditions' => 'required|string',
            'additional_info' => 'nullable|string',
            'require_digital_signature' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($request->project_source === 'existing') {
            $existingProject = Project::where('crm_project_id', $request->existing_project_id)->first();
            if ($existingProject && $existingProject->id !== $project->id) {
                return redirect()->back()->withErrors(['existing_project_id' => 'Project ini sudah terdaftar.']);
            }
        }

        DB::transaction(function () use ($request, $project) {
            $oldData = $project->only(['name', 'is_active']);

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($project->logo && Storage::disk('public')->exists($project->logo)) {
                    Storage::disk('public')->delete($project->logo);
                }
                $project->logo = $request->file('logo')->store('projects/logos', 'public');
            }

            // Determine project name and description
            if ($request->project_source === 'existing') {
                $crmProject = ProjectCRM::findOrFail($request->existing_project_id);
                $projectName = $crmProject->nama_project;
                $crmProjectId = $request->existing_project_id;
            } else {
                $projectName = $request->name;
                $crmProjectId = null;
            }

            // Update project
            $project->update([
                'name' => $projectName,
                'location' => $request->location,
                'description' => $request->description,
                'terms_and_conditions' => $request->terms_and_conditions,
                'additional_info' => $request->additional_info,
                'require_digital_signature' => $request->boolean('require_digital_signature', true),
                'is_agreement_accepted' => true,
                'agreement_sign' => 'auto',
                'is_active' => $request->boolean('is_active', true),
                'crm_project_id' => $crmProjectId,
            ]);

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'update_project',
                "Project diperbarui: {$project->name}",
                $project->id,
                ['old_data' => $oldData, 'new_data' => $project->only(['name', 'is_active'])]
            );
        });

        return redirect()->route('superadmin.projects.index')
            ->with('success', 'Project berhasil diperbarui!');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        DB::transaction(function () use ($project) {
            // Check if project has affiliators
            if ($project->affiliatorProjects()->count() > 0) {
                throw new \Exception('Project tidak dapat dihapus karena masih memiliki affiliator.');
            }

            // Check if project has leads
            if ($project->leads()->count() > 0) {
                throw new \Exception('Project tidak dapat dihapus karena masih memiliki lead.');
            }

            $projectName = $project->name;

            // Delete logo
            if ($project->logo && Storage::disk('public')->exists($project->logo)) {
                Storage::disk('public')->delete($project->logo);
            }

            // Get admin IDs before deletion
            $adminIds = $project->admins->pluck('id')->toArray();

            // Delete project
            $project->delete();

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'delete_project',
                "Project dihapus: {$projectName}"
            );

            // Send notifications to admins
            if (!empty($adminIds)) {
                $this->notificationService->broadcast(
                    $adminIds,
                    'Project Dihapus',
                    "Project {$projectName} telah dihapus dari sistem",
                    'warning'
                );
            }
        });

        return redirect()->route('superadmin.projects.index')
            ->with('success', 'Project berhasil dihapus!');
    }

    /**
     * Toggle project status.
     */
    public function toggleStatus(Project $project)
    {
        $newStatus = !$project->is_active;
        $project->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        $this->activityLogService->log(
            Auth::id(),
            'toggle_project_status',
            "Project {$project->name} {$statusText}",
            $project->id
        );

        // Notify project admins
        $this->notificationService->notifyProjectAdmins(
            $project->id,
            "Project Status Update",
            "Project {$project->name} telah {$statusText}",
            $newStatus ? 'success' : 'warning'
        );

        return back()->with('success', "Project berhasil {$statusText}!");
    }

    /**
     * Get CRM projects for Select2 with pagination and search
     */
    public function getCrmProjects(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = ProjectCRM::query();

        if ($search) {
            $query->where('nama_project', 'like', "%{$search}%");
        }

        $projects = $query->orderBy('nama_project')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'results' => $projects->map(function ($project) {
                return [
                    'id' => $project->id,
                    'text' => $project->nama_project,
                ];
            }),
            'pagination' => [
                'more' => $projects->hasMorePages()
            ]
        ]);
    }

    /**
     * Get specific CRM project details
     */
    public function getCrmProjectDetails(Request $request, $id)
    {
        $project = ProjectCRM::find($id);
        
        if (!$project) {
            return response()->json(['error' => 'Project tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $project->id,
            'name' => $project->nama_project,
        ]);
    }
}