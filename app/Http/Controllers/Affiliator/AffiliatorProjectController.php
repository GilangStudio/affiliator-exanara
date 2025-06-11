<?php

namespace App\Http\Controllers\Affiliator;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\AffiliatorProject;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;

class AffiliatorProjectController extends Controller
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
     * Display all projects and user's joined projects
     */
    public function index(Request $request)
    {
        $user = User::findOrFail(Auth::id());
        
        // Get all active projects
        $allProjectsQuery = Project::active()
            ->withCount([
                'affiliatorProjects as total_affiliators',
                'affiliatorProjects as active_affiliators' => function($query) {
                    $query->where('status', 'active');
                },
                'units as total_units' => function($query) {
                    $query->where('is_active', true);
                }
            ]);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $allProjectsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply location filter
        if ($request->filled('location')) {
            $allProjectsQuery->where('location', 'like', '%' . $request->location . '%');
        }

        // $allProjects = $allProjectsQuery->orderBy('created_at', 'desc')->get();

        // get all projects that user is not joined yet
        $allProjects = $allProjectsQuery->get()
            ->filter(function($project) use ($user) {
                return !$user->affiliatorProjects()->where('project_id', $project->id)->exists();
            });

        // Get user's joined projects with status
        $userProjects = $user->affiliatorProjects()
            ->with(['project' => function($q) {
                $q->withCount([
                    'affiliatorProjects as total_affiliators',
                    'units as total_units' => function($query) {
                        $query->where('is_active', true);
                    }
                ]);
            }])
            ->get()
            ->keyBy('project_id');

        // Get system settings
        $maxProjects = SystemSetting::getValue('max_projects_per_affiliator', 3);
        $currentProjectCount = $user->affiliatorProjects()->count();

        // Get unique locations for filter
        $locations = Project::active()
            ->whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->filter()
            ->sort()
            ->values();

        return view('pages.affiliator.projects.index', compact(
            'allProjects',
            'userProjects', 
            'maxProjects',
            'currentProjectCount',
            'locations'
        ));
    }

    /**
     * Show project details
     */
    public function show(Project $project)
    {
        if (!$project->is_active) {
            return redirect()->route('affiliator.project.index')
                ->with('error', 'Project tidak aktif atau tidak ditemukan.');
        }

        $user = User::findOrFail(Auth::id());
        
        // Load project with relationships
        $project->load([
            'units' => function($query) {
                $query->active()->orderBy('price');
            },
            'faqs' => function($query) {
                $query->active()->ordered();
            }
        ]);

        // Get user's status for this project
        $userProject = $user->affiliatorProjects()
            ->where('project_id', $project->id)
            ->first();

        // Get project statistics
        $stats = [
            'total_affiliators' => $project->affiliatorProjects()->count(),
            'active_affiliators' => $project->affiliatorProjects()->where('status', 'active')->count(),
            'total_units' => $project->units()->active()->count(),
            'commission_info' => $project->commission_info
        ];

        // Check if user can join
        $canJoin = $project->canBeJoinedBy($user);

        return view('pages.affiliator.projects.show', compact(
            'project',
            'userProject',
            'stats',
            'canJoin'
        ));
    }

    /**
     * Join a project
     */
    public function join(Request $request, Project $project)
    {
        if (!$project->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Project tidak aktif atau tidak ditemukan.'
            ], 404);
        }

        $user = Auth::user();

        // Check if user can join this project
        $canJoin = $project->canBeJoinedBy($user);
        
        if (!$canJoin['can_join']) {
            return response()->json([
                'success' => false,
                'message' => $canJoin['reason']
            ], 400);
        }

        try {
            DB::transaction(function () use ($user, $project) {
                // Create affiliator project record
                AffiliatorProject::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'verification_status' => 'pending',
                    'status' => 'incomplete'
                ]);

                // Log activity
                $this->activityLogService->logAffiliatorProjectActivity(
                    $user->id,
                    'join_project',
                    $project->id,
                    "Bergabung dengan project: {$project->name}"
                );

                // Send notification to user
                $this->notificationService->createForUser(
                    $user->id,
                    'Berhasil Bergabung dengan Project',
                    "Anda berhasil bergabung dengan project {$project->name}. Silakan lengkapi data dan upload KTP untuk verifikasi.",
                    'success',
                    ['project_id' => $project->id]
                );

                // Notify project admins
                $this->notificationService->notifyProjectAdmins(
                    $project->id,
                    'Affiliator Baru Bergabung',
                    "Affiliator {$user->name} bergabung dengan project {$project->name}",
                    'info',
                    ['user_id' => $user->id, 'project_id' => $project->id]
                );
            });

            return response()->json([
                'success' => true,
                'message' => "Berhasil bergabung dengan project {$project->name}!",
                'redirect' => route('affiliator.project.index')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle project status (activate/deactivate)
     */
    public function toggleStatus(Request $request, Project $project)
    {
        $user = User::findOrFail(Auth::id());
        
        $affiliatorProject = $user->affiliatorProjects()
            ->where('project_id', $project->id)
            ->first();

        if (!$affiliatorProject) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak terdaftar di project ini.'
            ], 404);
        }

        // Check if user has pending leads when trying to deactivate
        if ($affiliatorProject->status === 'active') {
            $pendingLeads = $affiliatorProject->leads()->where('verification_status', 'pending')->count();
            
            if ($pendingLeads > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Tidak dapat menonaktifkan project karena masih ada {$pendingLeads} lead yang belum diverifikasi."
                ], 400);
            }
        }

        try {
            DB::transaction(function () use ($user, $project, $affiliatorProject) {
                // Toggle status
                $newStatus = $affiliatorProject->status === 'active' ? 'suspended' : 'active';
                $affiliatorProject->update(['status' => $newStatus]);

                $statusText = $newStatus === 'active' ? 'diaktifkan' : 'dinonaktifkan';

                // Log activity
                $this->activityLogService->log(
                    $user->id,
                    $newStatus === 'active' ? 'activate_project' : 'deactivate_project',
                    "Project {$project->name} {$statusText}",
                    $project->id
                );

                // Send notification to user
                $this->notificationService->createForUser(
                    $user->id,
                    'Status Project Diubah',
                    "Project {$project->name} telah {$statusText}.",
                    $newStatus === 'active' ? 'success' : 'warning',
                    ['project_id' => $project->id, 'status' => $newStatus]
                );

                // Notify project admins
                $this->notificationService->notifyProjectAdmins(
                    $project->id,
                    'Affiliator Mengubah Status Project',
                    "Affiliator {$user->name} {$statusText} partisipasi di project {$project->name}",
                    'info',
                    ['user_id' => $user->id, 'project_id' => $project->id, 'status' => $newStatus]
                );
            });

            $statusText = $affiliatorProject->fresh()->status === 'active' ? 'diaktifkan' : 'dinonaktifkan';

            return response()->json([
                'success' => true,
                'message' => "Project {$project->name} berhasil {$statusText}.",
                'new_status' => $affiliatorProject->fresh()->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get project statistics for AJAX
     */
    public function statistics(Project $project)
    {
        $stats = [
            'total_affiliators' => $project->affiliatorProjects()->count(),
            'active_affiliators' => $project->affiliatorProjects()->where('status', 'active')->count(),
            'total_units' => $project->units()->active()->count(),
            'total_leads' => $project->leads()->count(),
            'verified_leads' => $project->leads()->verified()->count(),
            'commission_info' => $project->commission_info
        ];

        return response()->json($stats);
    }

    /**
     * Get available locations for filter
     */
    public function getLocations()
    {
        $locations = Project::active()
            ->whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->filter()
            ->sort()
            ->values();

        return response()->json($locations);
    }
}