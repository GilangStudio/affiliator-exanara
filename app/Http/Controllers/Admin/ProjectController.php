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

        // Filter by registration status
        if ($request->filled('registration_status')) {
            $query->where('projects.registration_status', $request->registration_status);
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
        // Check if current user can view the project
        if (!User::find(Auth::user()->id)->canViewProject($project->id)) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat project ini.');
        }

        $project->load([
            'admins',
            'affiliatorProjects.user',
            'leads',
            'units',
            'latestRegistration.submittedBy',
            'latestRegistration.reviewedBy'
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

        // Check if current user is PIC of this project
        $isPic = User::find(Auth::user()->id)->isPicOfProject($project->id);

        return view('pages.admin.projects.show', compact('project', 'stats', 'recentLeads', 'isPic'));
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project)
    {
        // Check if current user can view the project
        if (!User::find(Auth::user()->id)->canViewProject($project->id)) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat project ini.');
        }

        // Check if current user can edit the project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.show', $project)
                ->with('error', 'Hanya admin PIC yang dapat mengedit project ini.');
        }

        // Check if project can be edited based on registration status
        if ($project->is_manual_registration && $project->registration_status === 'pending') {
            return redirect()->route('admin.projects.show', $project)
                ->with('error', 'Project tidak dapat diedit saat menunggu persetujuan Super Admin.');
        }

        $project->load([
            'latestRegistration.submittedBy',
            'latestRegistration.reviewedBy'
        ]);

        return view('pages.admin.projects.edit', compact('project'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Hanya admin PIC yang dapat mengedit project ini.');
        }

        // Check if project can be edited based on registration status
        if ($project->is_manual_registration && $project->registration_status === 'pending') {
            return redirect()->route('admin.projects.show', $project)
                ->with('error', 'Project tidak dapat diedit saat menunggu persetujuan Super Admin.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'developer_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brochure_file' => 'nullable|mimes:pdf|max:10240',
            'price_list_file' => 'nullable|mimes:pdf|max:10240',
            'terms_and_conditions' => 'required|string',
            'additional_info' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'commission_payment_trigger' => 'nullable|in:booking_fee,akad_kredit,spk',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'pic_email' => 'nullable|email|max:255|unique:users,email,' . ($project->pic_user_id ?: 'NULL'),
            'require_digital_signature' => 'boolean',
            'is_active' => 'boolean'
        ], [
            'name.required' => 'Nama project wajib diisi.',
            'terms_and_conditions.required' => 'Syarat dan ketentuan wajib diisi.',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
            'pic_email.unique' => 'Email PIC sudah digunakan oleh user lain.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'brochure_file.max' => 'Ukuran file brosur maksimal 10MB.',
            'price_list_file.max' => 'Ukuran file price list maksimal 10MB.',
        ]);

        $updatedProject = DB::transaction(function () use ($request, $project) {
            $oldData = $project->only(['name', 'is_active', 'pic_user_id']);

            // Handle file uploads with replacement
            if ($request->hasFile('logo')) {
                // Delete old logo
                if ($project->logo && Storage::disk('public')->exists($project->logo)) {
                    Storage::disk('public')->delete($project->logo);
                }
                $project->logo = $request->file('logo')->store('projects/logos', 'public');
            }

            if ($request->hasFile('brochure_file')) {
                // Delete old brochure
                if ($project->brochure_file && Storage::disk('public')->exists($project->brochure_file)) {
                    Storage::disk('public')->delete($project->brochure_file);
                }
                $project->brochure_file = $request->file('brochure_file')->store('projects/brochures', 'public');
            }

            if ($request->hasFile('price_list_file')) {
                // Delete old price list
                if ($project->price_list_file && Storage::disk('public')->exists($project->price_list_file)) {
                    Storage::disk('public')->delete($project->price_list_file);
                }
                $project->price_list_file = $request->file('price_list_file')->store('projects/price_lists', 'public');
            }

            // Handle PIC user management
            $picUserId = $project->pic_user_id;
            $picChanged = false;
            
            if ($request->filled('pic_name') && $request->filled('pic_email')) {
                // Check if PIC email changed
                if ($request->pic_email !== $project->pic_email) {
                    // Check if new email already exists
                    $existingUser = User::where('email', $request->pic_email)->first();
                    
                    if ($existingUser) {
                        $picUserId = $existingUser->id;
                        // Update existing user to be PIC if not already
                        if (!$existingUser->is_pic) {
                            $existingUser->update([
                                'name' => $request->pic_name,
                                'phone' => $request->pic_phone ?: $existingUser->phone,
                                'is_pic' => true,
                                'role' => 'admin',
                                'is_active' => true
                            ]);
                        }
                        $picChanged = true;
                    } else {
                        // Create new PIC user
                        $newPicUser = User::createPicUser(
                            $request->pic_name,
                            $request->pic_email,
                            $request->pic_phone ?: '0'
                        );
                        $newPicUser->update(['is_active' => true]);
                        $picUserId = $newPicUser->id;
                        $picChanged = true;
                        
                        // Remove old PIC from admin if exists and different
                        if ($project->pic_user_id && $project->pic_user_id !== $picUserId) {
                            $project->admins()->detach($project->pic_user_id);
                        }
                        
                        // Add new PIC as admin
                        $project->admins()->syncWithoutDetaching([$picUserId]);
                    }
                } else if ($project->picUser) {
                    // Update existing PIC user info
                    $project->picUser->update([
                        'name' => $request->pic_name,
                        'phone' => $request->pic_phone ?: $project->picUser->phone,
                    ]);
                }
            } else {
                // PIC info removed
                if ($project->pic_user_id) {
                    $picUserId = null;
                    $picChanged = true;
                    // Don't remove from admins, just unlink as PIC
                }
            }
            
            // Determine active status based on registration status
            $isActive = $project->registration_status === 'approved' 
                        ? $request->boolean('is_active', true) 
                        : false;

            // Update project
            $project->update([
                'name' => $request->name,
                'developer_name' => $request->developer_name,
                'location' => $request->location,
                'website_url' => $request->website_url,
                'description' => $request->description,
                'terms_and_conditions' => $request->terms_and_conditions,
                'additional_info' => $request->additional_info,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'commission_payment_trigger' => $request->commission_payment_trigger,
                'pic_name' => $request->pic_name,
                'pic_phone' => $request->pic_phone,
                'pic_email' => $request->pic_email,
                'pic_user_id' => $picUserId,
                'require_digital_signature' => $request->boolean('require_digital_signature', false),
                'is_active' => $isActive,
            ]);

            // Send notification to PIC if changed
            if ($picChanged && $picUserId && $picUserId !== ($oldData['pic_user_id'] ?? null)) {
                $this->notificationService->createForUser(
                    $picUserId,
                    'Ditunjuk sebagai PIC Project',
                    "Anda telah ditunjuk sebagai PIC untuk project '{$project->name}'.",
                    'info',
                    ['project_id' => $project->id, 'role' => 'pic']
                );
            }

            return $project;
        });

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'update_project',
            "Project diperbarui oleh admin PIC: {$updatedProject->name}",
            $updatedProject->id,
            [
                'changes' => $request->only([
                    'name', 'developer_name', 'location', 'website_url', 'pic_name', 'pic_email'
                ]),
                'files_updated' => [
                    'logo' => $request->hasFile('logo'),
                    'brochure' => $request->hasFile('brochure_file'),
                    'price_list' => $request->hasFile('price_list_file'),
                ]
            ]
        );

        return redirect()->route('admin.projects.show', $updatedProject)
            ->with('success', 'Project berhasil diperbarui!');
    }

    /**
     * Toggle project status.
     */
    public function toggleStatus(Project $project)
    {
        // Check if current user can edit the project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Hanya admin PIC yang dapat mengubah status project ini.');
        }

        // Only allow toggle if project is approved
        if ($project->is_manual_registration && $project->registration_status !== 'approved') {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Project harus disetujui terlebih dahulu sebelum dapat diaktifkan/nonaktifkan.');
        }

        $project->update([
            'is_active' => !$project->is_active
        ]);

        $status = $project->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'toggle_project_status',
            "Project {$status} oleh admin PIC: {$project->name}",
            $project->id,
            ['new_status' => $project->is_active]
        );

        return redirect()->back()
            ->with('success', "Project berhasil {$status}!");
    }

    /**
     * Show resubmit form for rejected project
     */
    public function showResubmitForm(Project $project)
    {
        // Check if current user can edit the project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Hanya admin PIC yang dapat mengajukan ulang project ini.');
        }

        // Only allow resubmit if project is rejected
        if ($project->registration_status !== 'rejected') {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Hanya project yang ditolak yang dapat diajukan ulang.');
        }

        // Only manual registration projects can be resubmitted
        if (!$project->is_manual_registration) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Project ini tidak memerlukan proses persetujuan.');
        }

        $project->load([
            'latestRegistration.submittedBy',
            'latestRegistration.reviewedBy'
        ]);

        return view('pages.admin.projects.resubmit', compact('project'));
    }

    /**
     * Resubmit rejected project for approval
     */
    public function resubmit(Project $project)
    {
        // Check if current user can edit the project (only PIC)
        if (!User::find(Auth::user()->id)->canEditProject($project->id)) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Hanya admin PIC yang dapat mengajukan ulang project ini.');
        }

        // Only allow resubmit if project is rejected
        if ($project->registration_status !== 'rejected') {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Hanya project yang ditolak yang dapat diajukan ulang.');
        }

        DB::transaction(function () use ($project) {
            // Update project status to pending
            $project->update([
                'registration_status' => 'pending',
                'rejection_reason' => null
            ]);

            // Update registration record if exists
            if ($project->latestRegistration) {
                $project->latestRegistration->update([
                    'status' => 'pending',
                    'reviewed_by' => null,
                    'reviewed_at' => null,
                    'review_notes' => null
                ]);
            }
        });

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'resubmit_project',
            "Project diajukan ulang untuk persetujuan: {$project->name}",
            $project->id
        );

        // Send notification to super admins
        $superAdmins = User::where('role', 'superadmin')->where('is_active', true)->get();
        foreach ($superAdmins as $superAdmin) {
            $this->notificationService->createForUser(
                $superAdmin->id,
                'Project Diajukan Ulang',
                "Project '{$project->name}' telah diajukan ulang untuk ditinjau setelah sebelumnya ditolak.",
                'info',
                ['project_id' => $project->id, 'action' => 'resubmit']
            );
        }

        return redirect()->route('admin.projects.show', $project)
            ->with('success', 'Project berhasil diajukan ulang untuk persetujuan Super Admin!');
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