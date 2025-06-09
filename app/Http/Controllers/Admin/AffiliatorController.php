<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Project;
use App\Models\AffiliatorProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AffiliatorController extends Controller
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
     * Display all affiliators from projects managed by current admin.
     */
    public function index(Request $request)
    {
        // Get all projects managed by current admin
        $adminProjects = User::find(Auth::user()->id)->adminProjects()->pluck('projects.id');

        $query = AffiliatorProject::whereIn('project_id', $adminProjects)
            ->with(['user', 'project', 'verifiedBy']);

        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter by verification status
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                })
                ->orWhereHas('project', function($projectQuery) use ($search) {
                    $projectQuery->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $affiliators = $query->paginate(15);

        // Get projects for filter
        $projects = User::find(Auth::user()->id)->adminProjects()->get();

        // Statistics
        $stats = [
            'total' => AffiliatorProject::whereIn('project_id', $adminProjects)->count(),
            'pending' => AffiliatorProject::whereIn('project_id', $adminProjects)->where('verification_status', 'pending')->count(),
            'verified' => AffiliatorProject::whereIn('project_id', $adminProjects)->where('verification_status', 'verified')->count(),
            'active' => AffiliatorProject::whereIn('project_id', $adminProjects)->where('status', 'active')->count(),
            'suspended' => AffiliatorProject::whereIn('project_id', $adminProjects)->where('status', 'suspended')->count(),
        ];

        return view('pages.admin.affiliators.index', compact('affiliators', 'projects', 'stats'));
    }

    /**
     * Display the specified affiliator.
     */
    public function show(AffiliatorProject $affiliator)
    {
        // Check if current admin manages this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $affiliator->project_id)->exists()) {
            return redirect()->route('admin.affiliators.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat affiliator ini.');
        }

        $affiliator->load(['user', 'project', 'leads.unit', 'verifiedBy']);

        $stats = [
            'total_leads' => $affiliator->leads()->count(),
            'verified_leads' => $affiliator->leads()->verified()->count(),
            'pending_leads' => $affiliator->leads()->pending()->count(),
            'rejected_leads' => $affiliator->leads()->rejected()->count(),
            'total_commission' => $affiliator->leads()->verified()->sum('commission_earned'),
        ];

        // Get recent activities for this affiliator
        $recentActivities = $this->activityLogService->getRecentActivities(
            10, 
            null, // Don't filter by user_id to get both user and admin actions
            $affiliator->project_id,
            ['verify_ktp', 'reject_ktp', 'reset_password', 'toggle_affiliator_status', 'update_affiliator', 'upload_ktp', 'join_project']
        )->filter(function($activity) use ($affiliator) {
            // Filter activities related to this specific affiliator
            return $activity->user_id == $affiliator->user_id || 
                   (isset($activity->properties['affiliator_project_id']) && $activity->properties['affiliator_project_id'] == $affiliator->id);
        });

        return view('pages.admin.affiliators.show', compact('affiliator', 'stats', 'recentActivities'));
    }

    /**
     * Show the form for editing the specified affiliator.
     */
    public function edit(AffiliatorProject $affiliator)
    {
        // Check if current admin manages this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $affiliator->project_id)->exists()) {
            return redirect()->route('admin.affiliators.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit affiliator ini.');
        }

        $affiliator->load(['user', 'project']);

        return view('pages.admin.affiliators.edit', compact('affiliator'));
    }

    /**
     * Update the specified affiliator.
     */
    public function update(Request $request, AffiliatorProject $affiliator)
    {
        // Check if current admin manages this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $affiliator->project_id)->exists()) {
            return redirect()->route('admin.affiliators.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit affiliator ini.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $affiliator->user_id,
            'phone' => 'required|string|max:20|unique:users,phone,' . $affiliator->user_id,
            'verification_status' => 'required|in:pending,verified,rejected',
            'status' => 'required|in:incomplete,pending_verification,active,suspended',
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($request, $affiliator) {
            // Update user data
            $affiliator->user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ]);

            // Update affiliator project data
            $oldStatus = $affiliator->verification_status;
            $affiliator->update([
                'verification_status' => $request->verification_status,
                'status' => $request->status,
                'verification_notes' => $request->verification_notes,
                'verified_by' => Auth::id(),
                'verified_at' => $request->verification_status === 'verified' ? now() : null,
            ]);

            // Log activity
            $this->activityLogService->logAffiliatorProjectActivity(
                Auth::id(),
                'update_affiliator',
                $affiliator->project_id,
                "Data affiliator {$affiliator->user->name} diperbarui untuk project {$affiliator->project->name}",
                [
                    'affiliator_project_id' => $affiliator->id,
                    'old_verification_status' => $oldStatus,
                    'new_verification_status' => $request->verification_status,
                    'old_status' => $affiliator->getOriginal('status'),
                    'new_status' => $request->status
                ]
            );

            // Send notification if status changed
            if ($oldStatus !== $request->verification_status) {
                $this->notificationService->sendKtpVerificationNotification(
                    $affiliator->id,
                    $request->verification_status,
                    $request->verification_notes
                );
            }
        });

        return redirect()->route('admin.affiliators.index')
            ->with('success', 'Data affiliator berhasil diperbarui!');
    }

    /**
     * Verify KTP data.
     */
    public function verifyKtp(Request $request, AffiliatorProject $affiliator)
    {
        // Check if current admin manages this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $affiliator->project_id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
            'action' => 'required|in:verify,reject'
        ]);

        DB::transaction(function () use ($request, $affiliator) {
            $isVerified = $request->action === 'verify';
            
            $affiliator->update([
                'verification_status' => $isVerified ? 'verified' : 'rejected',
                'verified_at' => $isVerified ? now() : null,
                'verified_by' => Auth::id(),
                'verification_notes' => $request->verification_notes,
                'status' => $isVerified ? 'active' : 'incomplete'
            ]);

            // Log activity
            $action = $isVerified ? 'verify_ktp' : 'reject_ktp';
            $this->activityLogService->log(
                Auth::id(),
                $action,
                "KTP affiliator {$affiliator->user->name} " . ($isVerified ? 'diverifikasi' : 'ditolak') . " untuk project {$affiliator->project->name}",
                $affiliator->project_id,
                ['affiliator_id' => $affiliator->id]
            );

            // Send notification
            $message = $isVerified 
                ? "KTP Anda untuk project {$affiliator->project->name} telah diverifikasi. Anda sekarang dapat mulai menambahkan lead."
                : "Verifikasi KTP Anda untuk project {$affiliator->project->name} ditolak. Alasan: {$request->verification_notes}";

            $this->notificationService->createForUser(
                $affiliator->user_id,
                $isVerified ? 'KTP Diverifikasi' : 'KTP Ditolak',
                $message,
                $isVerified ? 'success' : 'error'
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'KTP berhasil ' . ($request->action === 'verify' ? 'diverifikasi' : 'ditolak') . '!'
        ]);
    }

    /**
     * Reset password.
     */
    public function resetPassword(AffiliatorProject $affiliator)
    {
        // Check if current admin manages this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $affiliator->project_id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newPassword = Str::random(8);
        
        $affiliator->user->update([
            'password' => Hash::make($newPassword)
        ]);

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'reset_password',
            "Password affiliator {$affiliator->user->name} direset untuk project {$affiliator->project->name}",
            $affiliator->project_id,
            ['affiliator_id' => $affiliator->id]
        );

        // Send notification with new password
        $this->notificationService->createForUser(
            $affiliator->user_id,
            'Password Direset',
            "Password Anda telah direset oleh admin. Password baru: {$newPassword}. Silakan login dan ubah password Anda segera.",
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => "Password berhasil direset! Password baru: {$newPassword}",
            'password' => $newPassword
        ]);
    }

    /**
     * Toggle user status.
     */
    public function toggleStatus(AffiliatorProject $affiliator)
    {
        // Check if current admin manages this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $affiliator->project_id)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $newStatus = !$affiliator->user->is_active;
        $affiliator->user->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'toggle_affiliator_status',
            "Affiliator {$affiliator->user->name} {$statusText} untuk project {$affiliator->project->name}",
            $affiliator->project_id,
            ['affiliator_id' => $affiliator->id, 'new_status' => $newStatus]
        );

        // Send notification
        $this->notificationService->createForUser(
            $affiliator->user_id,
            'Status Akun Diperbarui',
            "Status akun Anda untuk project {$affiliator->project->name} telah {$statusText}.",
            $newStatus ? 'success' : 'warning'
        );

        return response()->json([
            'success' => true,
            'message' => "Affiliator berhasil {$statusText}!",
            'new_status' => $newStatus
        ]);
    }

    /**
     * Get affiliator statistics.
     */
    public function statistics()
    {
        $adminProjects = User::find(Auth::user()->id)->adminProjects()->pluck('projects.id');

        $stats = [
            'total_affiliators' => AffiliatorProject::whereIn('project_id', $adminProjects)->count(),
            'pending_verification' => AffiliatorProject::whereIn('project_id', $adminProjects)->where('verification_status', 'pending')->count(),
            'verified_affiliators' => AffiliatorProject::whereIn('project_id', $adminProjects)->where('verification_status', 'verified')->count(),
            'active_affiliators' => AffiliatorProject::whereIn('project_id', $adminProjects)->where('status', 'active')->count(),
            'suspended_affiliators' => AffiliatorProject::whereIn('project_id', $adminProjects)->where('status', 'suspended')->count(),
            'total_leads' => DB::table('leads')
                ->join('affiliator_projects', 'leads.affiliator_project_id', '=', 'affiliator_projects.id')
                ->whereIn('affiliator_projects.project_id', $adminProjects)
                ->count(),
            'verified_leads' => DB::table('leads')
                ->join('affiliator_projects', 'leads.affiliator_project_id', '=', 'affiliator_projects.id')
                ->whereIn('affiliator_projects.project_id', $adminProjects)
                ->where('leads.verification_status', 'verified')
                ->count(),
        ];

        return response()->json($stats);
    }
}