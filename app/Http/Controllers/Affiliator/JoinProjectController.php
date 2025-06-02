<?php

namespace App\Http\Controllers\Affiliator;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\AffiliatorProject;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class JoinProjectController extends Controller
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
     * Display available projects to join
     */
    public function index()
    {
        $user = User::find(Auth::user()->id);
        
        // Get joined project IDs
        $joinedProjectIds = $user->affiliatorProjects()->pluck('project_id');
        
        // Get available projects with additional info
        $availableProjects = Project::active()
            ->whereNotIn('id', $joinedProjectIds)
            ->orderBy('name')
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'slug' => $project->slug,
                    'location' => $project->location,
                    'description' => $project->description,
                    'logo_url' => $project->logo_url ?: asset('images/default-project.png'),
                    'commission_type' => $project->commission_type,
                    'commission_value' => $project->commission_value,
                    'commission_display' => $project->commission_display,
                    'terms_and_conditions' => $project->terms_and_conditions,
                    'additional_info' => $project->additional_info,
                    'require_digital_signature' => $project->require_digital_signature,
                    'is_active' => $project->is_active,
                    // Additional computed fields
                    'total_affiliators' => $project->affiliatorProjects()->count(),
                    'active_affiliators' => $project->affiliatorProjects()->where('status', 'active')->count(),
                    'created_at_formatted' => $project->created_at->format('d M Y'),
                ];
            });

        // Get user's current projects with status
        $userProjects = $user->affiliatorProjects()
            ->with('project')
            ->get()
            ->map(function ($affiliatorProject) {
                return [
                    'project' => $affiliatorProject->project,
                    'status' => $affiliatorProject->status,
                    'verification_status' => $affiliatorProject->verification_status,
                ];
            });

        return view('pages.affiliator.join-project', compact('availableProjects', 'userProjects'));
    }

    /**
     * Get available projects for AJAX
     * Logic dipindah dari routes ke sini
     */
    public function getAvailableProjects()
    {
        $user = User::findOrFail(Auth::user()->id);
        $joinedProjectIds = $user->affiliatorProjects()->pluck('project_id');
        
        $projects = Project::active()
            ->whereNotIn('id', $joinedProjectIds)
            ->select('id', 'name', 'description', 'logo', 'commission_type', 'commission_value')
            ->get()
            ->map(function($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'logo_url' => $project->logo_url,
                    'commission_display' => $project->commission_display,
                    'total_affiliators' => $project->affiliatorProjects()->count(),
                    'active_affiliators' => $project->affiliatorProjects()->where('status', 'active')->count()
                ];
            });
        
        return response()->json($projects);
    }

    /**
     * Check if user can join more projects
     * Logic dipindah dari routes ke sini
     */
    public function checkCanJoin()
    {
        $user = User::findOrFail(Auth::user()->id);
        $maxProjects = \App\Models\SystemSetting::getValue('max_projects_per_affiliator', 3);
        $currentCount = $user->affiliatorProjects()->count();
        
        return response()->json([
            'can_join' => $currentCount < $maxProjects,
            'current_count' => $currentCount,
            'max_projects' => $maxProjects,
            'remaining' => $maxProjects - $currentCount
        ]);
    }

    /**
     * Validate project selection
     * Logic dipindah dari routes ke sini
     */
    public function validateProject(Request $request)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);
        
        $user = User::findOrFail(Auth::user()->id);
        $project = Project::findOrFail($request->project_id);
        
        // Check if already joined
        $alreadyJoined = $user->affiliatorProjects()
            ->where('project_id', $project->id)
            ->exists();
        
        if ($alreadyJoined) {
            return response()->json([
                'valid' => false,
                'message' => 'Anda sudah bergabung dengan project ini'
            ]);
        }
        
        // Check if project is active
        if (!$project->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'Project ini sedang tidak aktif'
            ]);
        }
        
        // Check max projects limit
        $maxProjects = \App\Models\SystemSetting::getValue('max_projects_per_affiliator', 3);
        $currentCount = $user->affiliatorProjects()->count();
        
        if ($currentCount >= $maxProjects) {
            return response()->json([
                'valid' => false,
                'message' => "Anda sudah mencapai batas maksimal {$maxProjects} project"
            ]);
        }
        
        // Check max affiliators per project if setting exists
        $maxAffiliators = \App\Models\SystemSetting::getValue('max_affiliators_per_project', null);
        if ($maxAffiliators) {
            $currentAffiliators = $project->affiliatorProjects()->count();
            
            if ($currentAffiliators >= $maxAffiliators) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Project sudah mencapai batas maksimal affiliator'
                ]);
            }
        }
        
        return response()->json([
            'valid' => true,
            'message' => 'Project dapat diikuti'
        ]);
    }

    /**
     * Get project details for AJAX request
     */
    public function getProjectDetails(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $user = User::findOrFail(Auth::user()->id);
        
        // Check if user already joined this project
        $existingProject = $user->affiliatorProjects()
            ->where('project_id', $project->id)
            ->first();

        if ($existingProject) {
            return response()->json([
                'error' => 'Anda sudah bergabung dengan project ini',
                'status' => 'already_joined'
            ], 400);
        }

        // Return detailed project information
        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'description' => $project->description,
            'logo_url' => $project->logo_url,
            'commission_info' => [
                'type' => $project->commission_type,
                'value' => $project->commission_value,
                'display' => $project->commission_display,
                'description' => $project->commission_type === 'percentage' 
                    ? "Anda akan mendapat {$project->commission_value}% dari setiap lead yang terverifikasi"
                    : "Anda akan mendapat Rp " . number_format($project->commission_value, 0, ',', '.') . " per lead terverifikasi"
            ],
            'terms_and_conditions' => $project->terms_and_conditions,
            'additional_info' => $project->additional_info,
        ]);
    }

    /**
     * Join a project with digital signature
     */
    public function joinProject(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'digital_signature' => 'required|string',
            'terms_accepted' => 'required|accepted'
        ], [
            'project_id.required' => 'Pilih project terlebih dahulu',
            'project_id.exists' => 'Project tidak valid',
            'digital_signature.required' => 'Tanda tangan digital diperlukan',
            'terms_accepted.required' => 'Anda harus menyetujui syarat & ketentuan',
            'terms_accepted.accepted' => 'Anda harus menyetujui syarat & ketentuan'
        ]);

        $user = User::findOrFail(Auth::user()->id);
        $project = Project::findOrFail($request->project_id);

        // Validation menggunakan method yang sudah ada
        $canJoin = $project->canBeJoinedBy($user);
        if (!$canJoin['can_join']) {
            return response()->json([
                'success' => false,
                'message' => $canJoin['reason']
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create affiliator project with initial data
            $affiliatorProject = AffiliatorProject::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'verification_status' => 'pending',
                'status' => 'incomplete',
                'terms_accepted' => true,
                'terms_accepted_at' => now(),
                'digital_signature' => $request->digital_signature,
                'digital_signature_at' => now()
            ]);

            // Log activity
            $this->activityLogService->logAffiliatorProjectActivity(
                $user->id,
                'join_project',
                $project->id,
                "Bergabung dengan project: {$project->name}",
                [
                    'project_name' => $project->name,
                    'has_digital_signature' => true,
                    'terms_accepted' => true,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            );

            // Notify project admins
            $this->notificationService->notifyProjectAdmins(
                $project->id,
                'Affiliator Baru Bergabung',
                "Affiliator {$user->name} telah bergabung dengan project {$project->name} dan menyetujui syarat & ketentuan",
                'info',
                [
                    'affiliator_id' => $user->id,
                    'affiliator_name' => $user->name,
                    'project_id' => $project->id,
                    'action_url' => route('admin.affiliators.show', $user->id)
                ]
            );

            // Send welcome notification to user
            $this->notificationService->createForUser(
                $user->id,
                'Selamat Bergabung!',
                "Anda telah berhasil bergabung dengan project {$project->name}. Langkah selanjutnya adalah melengkapi verifikasi KTP.",
                'success',
                ['action_url' => route('affiliator.setup.ktp', $project->slug)]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil bergabung dengan project {$project->name}",
                'next_step' => 'ktp_verification',
                'redirect_url' => route('affiliator.setup.ktp', $project->slug)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal bergabung dengan project: ' . $e->getMessage()
            ], 500);
        }
    }
}