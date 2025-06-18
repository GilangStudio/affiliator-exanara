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
                    $query->active();
                },
                'units as total_units' => function($query) {
                    $query->active();
                },
            ])
            ->whereHas('units', function ($query) {
                $query->active();
            });

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
                        $query->active();
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
            ->verified()
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
                $newStatus = $affiliatorProject->status === 'active' ? 'inactive' : 'active';
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
     * Show resubmit form for rejected verification
     */
    public function showResubmitForm(Project $project)
    {
        $user = User::findOrFail(Auth::id());
        
        $userProject = $user->affiliatorProjects()
            ->where('project_id', $project->id)
            ->first();

        if (!$userProject) {
            return redirect()->route('affiliator.project.index')
                ->with('error', 'Anda belum terdaftar di project ini.');
        }

        // Only allow resubmit if verification is rejected
        if ($userProject->verification_status !== 'rejected') {
            return redirect()->route('affiliator.project.show', $project)
                ->with('error', 'Data verifikasi hanya dapat disubmit ulang jika ditolak.');
        }

        $userProject->load(['verifiedBy']);

        return view('pages.affiliator.projects.resubmit', compact('project', 'userProject'));
    }

    /**
     * Process resubmit verification data
     */
    public function resubmit(Request $request, Project $project)
    {
        $user = User::findOrFail(Auth::id());
        
        $userProject = $user->affiliatorProjects()
            ->where('project_id', $project->id)
            ->first();

        if (!$userProject) {
            return redirect()->route('affiliator.project.index')
                ->with('error', 'Anda belum terdaftar di project ini.');
        }

        // Only allow resubmit if verification is rejected
        if ($userProject->verification_status !== 'rejected') {
            return redirect()->route('affiliator.project.show', $project)
                ->with('error', 'Data verifikasi hanya dapat disubmit ulang jika ditolak.');
        }

        // Validation rules
        $rules = [];
        $messages = [];

        // KTP validation - required if not exists, optional if exists
        if (!$userProject->ktp_number) {
            $rules['ktp_number'] = 'required|string|size:16';
            $messages['ktp_number.required'] = 'Nomor KTP harus diisi';
            $messages['ktp_number.size'] = 'Nomor KTP harus 16 digit';
        } else {
            $rules['ktp_number'] = 'nullable|string|size:16';
            $messages['ktp_number.size'] = 'Nomor KTP harus 16 digit';
        }

        if (!$userProject->ktp_photo) {
            $rules['ktp_photo'] = 'required|image|mimes:jpeg,png,jpg|max:2048';
            $messages['ktp_photo.required'] = 'Foto KTP harus diupload';
        } else {
            $rules['ktp_photo'] = 'nullable|image|mimes:jpeg,png,jpg|max:2048';
        }

        // Terms acceptance - required if not accepted
        if (!$userProject->terms_accepted) {
            $rules['accept_terms'] = 'required|accepted';
            $messages['accept_terms.required'] = 'Anda harus menyetujui syarat dan ketentuan';
        }

        // Digital signature - required if project requires it and not exists
        if ($project->require_digital_signature && !$userProject->digital_signature) {
            $rules['digital_signature'] = 'required|string';
            $messages['digital_signature.required'] = 'Tanda tangan digital harus dibuat';
        }

        $rules['notes'] = 'nullable|string|max:1000';

        $request->validate($rules, $messages);

        try {
            DB::transaction(function () use ($request, $userProject, $project, $user) {
                $updateData = [];

                // Update KTP data if provided
                if ($request->filled('ktp_number')) {
                    $updateData['ktp_number'] = $request->ktp_number;
                }

                if ($request->hasFile('ktp_photo')) {
                    // Delete old photo if exists
                    if ($userProject->ktp_photo && Storage::disk('public')->exists($userProject->ktp_photo)) {
                        Storage::disk('public')->delete($userProject->ktp_photo);
                    }
                    $updateData['ktp_photo'] = $request->file('ktp_photo')->store('ktp_photos', 'public');
                }

                // Update terms acceptance if provided
                if ($request->has('accept_terms')) {
                    $updateData['terms_accepted'] = true;
                    $updateData['terms_accepted_at'] = now();
                }

                // Update digital signature if provided
                if ($request->filled('digital_signature')) {
                    $updateData['digital_signature'] = $request->digital_signature;
                    $updateData['digital_signature_at'] = now();
                }

                // Reset verification status to pending
                $updateData['verification_status'] = 'pending';
                $updateData['verified_at'] = null;
                $updateData['verified_by'] = null;
                $updateData['verification_notes'] = null;

                // Update status based on completion
                $hasKtp = $updateData['ktp_number'] ?? $userProject->ktp_number;
                $hasKtpPhoto = $updateData['ktp_photo'] ?? $userProject->ktp_photo;
                $hasTerms = $updateData['terms_accepted'] ?? $userProject->terms_accepted;
                $hasSignature = $updateData['digital_signature'] ?? $userProject->digital_signature;

                if ($hasKtp && $hasKtpPhoto && $hasTerms && 
                    (!$project->require_digital_signature || $hasSignature)) {
                    $updateData['status'] = 'pending_verification';
                } else {
                    $updateData['status'] = 'incomplete';
                }

                $userProject->update($updateData);

                // Log activity
                $this->activityLogService->logAffiliatorProjectActivity(
                    $user->id,
                    'resubmit_verification',
                    $project->id,
                    "Data verifikasi disubmit ulang untuk project: {$project->name}",
                    [
                        'affiliator_project_id' => $userProject->id,
                        'notes' => $request->notes,
                        'updated_fields' => array_keys($updateData)
                    ]
                );

                // Send notification to user
                $this->notificationService->createForUser(
                    $user->id,
                    'Data Verifikasi Disubmit Ulang',
                    "Data verifikasi Anda untuk project {$project->name} telah disubmit ulang dan menunggu review admin.",
                    'info',
                    ['project_id' => $project->id, 'action' => 'resubmit']
                );

                // Notify project admins
                $this->notificationService->notifyProjectAdmins(
                    $project->id,
                    'Affiliator Submit Ulang Verifikasi',
                    "Affiliator {$user->name} telah submit ulang data verifikasi untuk project {$project->name}." . 
                    ($request->notes ? " Catatan: {$request->notes}" : ''),
                    'info',
                    [
                        'user_id' => $user->id, 
                        'project_id' => $project->id, 
                        'action' => 'resubmit',
                        'notes' => $request->notes
                    ]
                );
            });

            return redirect()->route('affiliator.project.show', $project)
                ->with('success', 'Data verifikasi berhasil disubmit ulang! Silakan tunggu review dari admin.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel project participation (only for pending or rejected verification)
     */
    public function cancelProject(Project $project)
    {
        $user = User::findOrFail(Auth::id());
        
        $userProject = $user->affiliatorProjects()
            ->where('project_id', $project->id)
            ->first();

        if (!$userProject) {
            return response()->json([
                'success' => false,
                'message' => 'Anda belum terdaftar di project ini.'
            ], 404);
        }

        // Only allow cancel if verification is pending or rejected
        if (!in_array($userProject->verification_status, ['pending', 'rejected'])) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membatalkan partisipasi karena verifikasi sudah disetujui.'
            ], 400);
        }

        // Check if user has pending leads
        $pendingLeads = $userProject->leads()->where('verification_status', 'pending')->count();
        if ($pendingLeads > 0) {
            return response()->json([
                'success' => false,
                'message' => "Tidak dapat membatalkan partisipasi karena masih ada {$pendingLeads} lead yang belum diverifikasi."
            ], 400);
        }

        try {
            DB::transaction(function () use ($user, $project, $userProject) {
                // Delete uploaded files
                if ($userProject->ktp_photo && Storage::disk('public')->exists($userProject->ktp_photo)) {
                    Storage::disk('public')->delete($userProject->ktp_photo);
                }
                
                if ($userProject->digital_signature && Storage::disk('public')->exists($userProject->digital_signature)) {
                    Storage::disk('public')->delete($userProject->digital_signature);
                }

                // Delete affiliator project record
                $userProject->delete();

                // Log activity
                $this->activityLogService->logAffiliatorProjectActivity(
                    $user->id,
                    'cancel_project',
                    $project->id,
                    "Membatalkan partisipasi di project: {$project->name}"
                );

                // Send notification to user
                $this->notificationService->createForUser(
                    $user->id,
                    'Partisipasi Project Dibatalkan',
                    "Partisipasi Anda di project {$project->name} telah dibatalkan.",
                    'info',
                    ['project_id' => $project->id, 'action' => 'cancel']
                );

                // Notify project admins
                $this->notificationService->notifyProjectAdmins(
                    $project->id,
                    'Affiliator Membatalkan Partisipasi',
                    "Affiliator {$user->name} membatalkan partisipasi di project {$project->name}",
                    'info',
                    ['user_id' => $user->id, 'project_id' => $project->id, 'action' => 'cancel']
                );
            });

            return response()->json([
                'success' => true,
                'message' => "Partisipasi di project {$project->name} berhasil dibatalkan.",
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