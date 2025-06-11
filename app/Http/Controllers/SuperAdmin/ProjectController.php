<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Models\User;
use App\Models\Project;
use App\Models\ProjectAdmin;
use Illuminate\Http\Request;
use App\Models\CRM\ProjectCRM;
use App\Models\ProjectRegistration;
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
     * Display a listing of projects dengan registration info.
     */
    public function index(Request $request)
    {
        $query = Project::with([
            'projectCrm:id,nama_project',
            'admins',
            'latestRegistration.submittedBy',
            'picUser'
        ])->withCount([
            'units',
            'affiliatorProjects',
            'leads',
            'leads as verified_leads_count' => function($query) {
                $query->verified();
            }
        ]);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status == 'active') {
                $query->where('is_active', true);
            } elseif ($request->status == 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Filter by registration type
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        // Filter by registration status
        if ($request->filled('registration_status')) {
            $query->where('registration_status', $request->registration_status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('developer_name', 'like', "%{$search}%")
                  ->orWhereHas('latestRegistration.submittedBy', function($subq) use ($search) {
                      $subq->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $projects = $query->paginate(15);

        // Statistics
        $statistics = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('is_active', true)->count(),
            'manual_projects' => Project::where('registration_type', 'manual')->count(),
            'crm_projects' => Project::where('registration_type', 'crm')->count(),
            'pending_registrations' => Project::where('registration_status', 'pending')->count(),
            'needs_review' => Project::where('registration_status', 'pending')->count(),
        ];

        return view('pages.superadmin.projects.index', compact('projects', 'statistics'));
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

            // Create project dengan registration_type 'crm' untuk superadmin
            $project = Project::create([
                'name' => $projectName,
                'location' => $request->location,
                'logo' => $logoPath,
                'description' => $request->description,
                'terms_and_conditions' => $request->terms_and_conditions,
                'additional_info' => $request->additional_info,
                'require_digital_signature' => $request->require_digital_signature == '1'? true : false,
                'is_active' => $request->boolean('is_active', true),
                'crm_project_id' => $request->project_source === 'existing' ? $request->existing_project_id : null,
                // Registration fields untuk superadmin projects
                'registration_type' => 'internal', // Ubah dari 'crm' ke 'internal'
                'registration_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
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
            'units',
            'latestRegistration.submittedBy',
            'picUser'
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
                'require_digital_signature' => $request->require_digital_signature == '1' ? true : false,
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

            // Delete files untuk manual registration
            if ($project->brochure_file && Storage::disk('public')->exists($project->brochure_file)) {
                Storage::disk('public')->delete($project->brochure_file);
            }
            
            if ($project->price_list_file && Storage::disk('public')->exists($project->price_list_file)) {
                Storage::disk('public')->delete($project->price_list_file);
            }

            // Get admin IDs before deletion
            $adminIds = $project->admins->pluck('id')->toArray();

            // Delete PIC user jika ada
            if ($project->picUser) {
                $project->picUser->delete();
            }

            // Delete project registrations
            $project->registrations()->delete();

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
     * Approve manual project registration
     */
    public function approveRegistration(Request $request, Project $project)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($project->registration_status !== 'pending' || $project->registration_type !== 'manual') {
            return back()->withErrors(['error' => 'Hanya manual registration dengan status pending yang dapat disetujui']);
        }

        try {
            DB::transaction(function () use ($project, $request) {
                // Update project status
                $project->update([
                    'registration_status' => 'approved',
                    'is_active' => true,
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);

                // Activate all units
                $project->units()->update(['is_active' => true]);

                // Activate PIC user
                if ($project->picUser) {
                    $project->picUser->update(['is_active' => true]);
                }

                // Update registration record
                if ($project->latestRegistration) {
                    $project->latestRegistration->update([
                        'status' => 'approved',
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                        'review_notes' => $request->notes
                    ]);
                }
            });

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'approve_project_registration',
                "Menyetujui registrasi project: {$project->name}",
                $project->id,
                [
                    'submitter_name' => $project->latestRegistration->submittedBy->name ?? 'Unknown',
                    'notes' => $request->notes
                ]
            );

            // Send notification to submitter
            if ($project->latestRegistration) {
                $this->notificationService->createForUser(
                    $project->latestRegistration->submitted_by,
                    'Project Disetujui!',
                    "Project '{$project->name}' telah disetujui dan sekarang aktif. Akun PIC telah diaktifkan.",
                    'success',
                    ['project_id' => $project->id]
                );
            }

            // Send notification to PIC
            if ($project->picUser) {
                $this->notificationService->createForUser(
                    $project->picUser->id,
                    'Akun Admin Project Aktif',
                    "Selamat! Anda telah ditunjuk sebagai PIC untuk project '{$project->name}'. Akun admin Anda telah diaktifkan.",
                    'success',
                    ['project_id' => $project->id, 'is_pic_notification' => true]
                );
            }

            return back()->with('success', 'Project registration berhasil disetujui!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menyetujui project registration: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject manual project registration
     */
    public function rejectRegistration(Request $request, Project $project)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ], [
            'reason.required' => 'Alasan penolakan harus diisi'
        ]);

        if ($project->registration_status !== 'pending' || $project->registration_type !== 'manual') {
            return back()->withErrors(['error' => 'Hanya manual registration dengan status pending yang dapat ditolak']);
        }

        try {
            DB::transaction(function () use ($project, $request) {
                // Update project status
                $project->update([
                    'registration_status' => 'rejected',
                    'rejection_reason' => $request->reason
                ]);

                // Update registration record
                if ($project->latestRegistration) {
                    $project->latestRegistration->update([
                        'status' => 'rejected',
                        'reviewed_by' => Auth::id(),
                        'reviewed_at' => now(),
                        'review_notes' => $request->reason
                    ]);
                }
            });

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'reject_project_registration',
                "Menolak registrasi project: {$project->name}",
                $project->id,
                [
                    'submitter_name' => $project->latestRegistration->submittedBy->name ?? 'Unknown',
                    'reason' => $request->reason
                ]
            );

            // Send notification to submitter
            if ($project->latestRegistration) {
                $this->notificationService->createForUser(
                    $project->latestRegistration->submitted_by,
                    'Project Ditolak',
                    "Project '{$project->name}' ditolak dengan alasan: {$request->reason}",
                    'error',
                    ['project_id' => $project->id, 'rejection_reason' => $request->reason]
                );
            }

            return back()->with('success', 'Project registration berhasil ditolak!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menolak project registration: ' . $e->getMessage()]);
        }
    }

    /**
     * Bulk approve pending registrations
     */
    public function bulkApproveRegistrations(Request $request)
    {
        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id'
        ]);

        $approved = 0;
        $errors = [];

        foreach ($request->project_ids as $projectId) {
            $project = Project::find($projectId);
            
            if ($project && $project->registration_status === 'pending' && $project->registration_type === 'manual') {
                try {
                    DB::transaction(function () use ($project) {
                        $project->update([
                            'registration_status' => 'approved',
                            'is_active' => true,
                            'approved_by' => Auth::id(),
                            'approved_at' => now()
                        ]);

                        $project->units()->update(['is_active' => true]);

                        if ($project->picUser) {
                            $project->picUser->update(['is_active' => true]);
                        }

                        if ($project->latestRegistration) {
                            $project->latestRegistration->update([
                                'status' => 'approved',
                                'reviewed_by' => Auth::id(),
                                'reviewed_at' => now(),
                                'review_notes' => 'Bulk approval'
                            ]);
                        }
                    });

                    $approved++;

                    // Send notifications
                    if ($project->latestRegistration) {
                        $this->notificationService->createForUser(
                            $project->latestRegistration->submitted_by,
                            'Project Disetujui!',
                            "Project '{$project->name}' telah disetujui dan sekarang aktif.",
                            'success'
                        );
                    }

                } catch (\Exception $e) {
                    $errors[] = "Gagal menyetujui {$project->name}: {$e->getMessage()}";
                }
            }
        }

        $message = "{$approved} project registration berhasil disetujui";
        
        if (!empty($errors)) {
            $message .= ". Errors: " . implode(', ', $errors);
        }

        return back()->with('success', $message);
    }

    /**
     * Show registration detail for manual projects
     */
    public function registrationDetail(Project $project)
    {
        if ($project->registration_type !== 'manual' || !$project->latestRegistration) {
            return redirect()->route('superadmin.projects.show', $project);
        }

        $registration = $project->latestRegistration;
        $registration->load(['submittedBy', 'reviewedBy']);

        return view('pages.superadmin.projects.registration-detail', compact('project', 'registration'));
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