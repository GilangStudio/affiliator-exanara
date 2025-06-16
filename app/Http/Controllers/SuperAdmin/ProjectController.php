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
use Illuminate\Validation\ValidationException;

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
        // Comprehensive validation
        $request->validate([
            'project_source' => 'required|in:new,existing',
            'name' => 'nullable|required_if:project_source,new|string|max:255',
            'developer_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'website_url' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'brochure_file' => 'nullable|mimes:pdf|max:10240',
            'price_list_file' => 'nullable|mimes:pdf|max:10240',
            'terms_and_conditions' => 'required|string',
            'additional_info' => 'nullable|string',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'commission_payment_trigger' => 'nullable|in:booking_fee,akad_kredit,spk',
            'pic_name' => 'nullable|string|max:255',
            'pic_phone' => 'nullable|string|max:20',
            'pic_email' => 'nullable|email|max:255|unique:users,email',
            'require_digital_signature' => 'boolean',
            'is_active' => 'boolean',
            'existing_project_id' => 'nullable|required_if:project_source,existing'
        ], [
            'existing_project_id.required_if' => 'Pilih project dari CRM.',
            'name.required_if' => 'Nama project wajib diisi.',
            'terms_and_conditions.required' => 'Syarat dan ketentuan wajib diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini.',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
            'pic_email.unique' => 'Email PIC sudah digunakan oleh user lain.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'brochure_file.max' => 'Ukuran file brosur maksimal 10MB.',
            'price_list_file.max' => 'Ukuran file price list maksimal 10MB.',
        ]);

        // Validate CRM project if existing source
        if ($request->project_source === 'existing') {
            $existingProject = Project::where('crm_project_id', $request->existing_project_id)->first();
            if ($existingProject) {
                throw ValidationException::withMessages([
                    'existing_project_id' => 'Project ini sudah terdaftar dalam sistem.'
                ]);
            }
        }

        // Validate PIC email if provided
        if ($request->filled('pic_email')) {
            $existingPicUser = User::where('email', $request->pic_email)->first();
            if ($existingPicUser && !$existingPicUser->is_pic) {
                throw ValidationException::withMessages([
                    'pic_email' => 'Email ini sudah digunakan oleh user dengan role berbeda.'
                ]);
            }
        }

        $project = DB::transaction(function () use ($request) {
            // Handle file uploads
            $logoPath = null;
            $brochurePath = null;
            $priceListPath = null;

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('projects/logos', 'public');
            }

            if ($request->hasFile('brochure_file')) {
                $brochurePath = $request->file('brochure_file')->store('projects/brochures', 'public');
            }

            if ($request->hasFile('price_list_file')) {
                $priceListPath = $request->file('price_list_file')->store('projects/price_lists', 'public');
            }

            // Determine project name based on source
            if ($request->project_source === 'existing') {
                $crmProject = ProjectCRM::findOrFail($request->existing_project_id);
                $projectName = $crmProject->nama_project;
                $crmProjectId = $request->existing_project_id;
            } else {
                $projectName = $request->name;
                $crmProjectId = null;
            }

            // Handle PIC user creation/assignment
            $picUserId = null;
            if ($request->filled('pic_name') && $request->filled('pic_email')) {
                // Check if user already exists
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
                } else {
                    // Create new PIC user
                    $picUser = User::createPicUser(
                        $request->pic_name,
                        $request->pic_email,
                        $request->pic_phone ?: '0'
                    );
                    $picUser->update(['is_active' => true]); // Activate immediately for superadmin created projects
                    $picUserId = $picUser->id;
                }
            }

            // Create project
            $project = Project::create([
                'name' => $projectName,
                'developer_name' => $request->developer_name,
                'location' => $request->location,
                'website_url' => $request->website_url,
                'logo' => $logoPath,
                'brochure_file' => $brochurePath,
                'price_list_file' => $priceListPath,
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
                'require_digital_signature' => $request->boolean('require_digital_signature', true),
                'is_active' => $request->boolean('is_active', true),
                'crm_project_id' => $crmProjectId,
                // Registration fields untuk superadmin projects
                'registration_type' => 'internal',
                'registration_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'is_agreement_accepted' => true,
                'agreement_sign' => 'superadmin_created',
            ]);

            // Add PIC as admin if created
            if ($picUserId) {
                $project->admins()->attach($picUserId);
                
                // Send notification to PIC
                $this->notificationService->createForUser(
                    $picUserId,
                    'Ditunjuk sebagai PIC Project',
                    "Anda telah ditunjuk sebagai PIC untuk project '{$project->name}'. Silakan login untuk mengelola project.",
                    'success',
                    ['project_id' => $project->id, 'role' => 'pic']
                );
            }

            return $project;
        });

        // Log activity
        $this->activityLogService->log(
            Auth::id(),
            'create_project',
            "Project baru dibuat: {$project->name}",
            $project->id,
            [
                'project_source' => $request->project_source,
                'pic_created' => $project->pic_user_id ? true : false,
                'has_files' => [
                    'logo' => $project->logo ? true : false,
                    'brochure' => $project->brochure_file ? true : false,
                    'price_list' => $project->price_list_file ? true : false,
                ]
            ]
        );

        return redirect()->route('superadmin.projects.show', $project)
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
            'latestRegistration.reviewedBy',
            'picUser'
        ]);

        $stats = [
            'total_affiliators' => $project->affiliatorProjects()->count(),
            'active_affiliators' => $project->affiliatorProjects()->where('status', 'active')->count(),
            'total_leads' => $project->leads()->count(),
            'verified_leads' => $project->leads()->verified()->count(),
            'total_units' => $project->units()->count(),
            'active_units' => $project->units()->active()->count(),
            'total_commission_paid' => $project->commissionHistories()->where('type', 'earned')->sum('amount'),
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
        $project->load([
            'admins',
            'units',
            'latestRegistration.submittedBy',
            'latestRegistration.reviewedBy',
            'picUser'
        ]);

        return view('pages.superadmin.projects.edit', compact('project'));
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        // Comprehensive validation
        $request->validate([
            'project_source' => 'required|in:new,existing',
            'name' => 'nullable|required_if:project_source,new|string|max:255',
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
            'is_active' => 'boolean',
            'existing_project_id' => 'nullable|required_if:project_source,existing'
        ], [
            'existing_project_id.required_if' => 'Pilih project dari CRM.',
            'name.required_if' => 'Nama project wajib diisi.',
            'terms_and_conditions.required' => 'Syarat dan ketentuan wajib diisi.',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
            'pic_email.unique' => 'Email PIC sudah digunakan oleh user lain.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'brochure_file.max' => 'Ukuran file brosur maksimal 10MB.',
            'price_list_file.max' => 'Ukuran file price list maksimal 10MB.',
        ]);

        // Validate CRM project if existing source
        if ($request->project_source === 'existing') {
            $existingProject = Project::where('crm_project_id', $request->existing_project_id)
                                    ->where('id', '!=', $project->id)
                                    ->first();
            if ($existingProject) {
                throw ValidationException::withMessages([
                    'existing_project_id' => 'Project ini sudah terdaftar dalam sistem.'
                ]);
            }
        }

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

            // Determine project name based on source
            if ($request->project_source === 'existing') {
                $crmProject = ProjectCRM::findOrFail($request->existing_project_id);
                $projectName = $crmProject->nama_project;
                $crmProjectId = $request->existing_project_id;
            } else {
                $projectName = $request->name;
                $crmProjectId = null;
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
            
            // Update project
            $project->update([
                'name' => $projectName,
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
                'is_active' => $request->boolean('is_active', true),
                'crm_project_id' => $crmProjectId,
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
            "Project diperbarui: {$updatedProject->name}",
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

        return redirect()->route('superadmin.projects.show', $updatedProject)
            ->with('success', 'Project berhasil diperbarui!');
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        // Check dependencies
        if ($project->affiliatorProjects()->count() > 0) {
            return back()->withErrors(['error' => 'Project tidak dapat dihapus karena masih memiliki affiliator.']);
        }

        if ($project->leads()->count() > 0) {
            return back()->withErrors(['error' => 'Project tidak dapat dihapus karena masih memiliki lead.']);
        }

        DB::transaction(function () use ($project) {
            $projectName = $project->name;

            // Delete files
            $filesToDelete = [
                $project->logo,
                $project->brochure_file,
                $project->price_list_file
            ];

            foreach ($filesToDelete as $file) {
                if ($file && Storage::disk('public')->exists($file)) {
                    Storage::disk('public')->delete($file);
                }
            }

            // Get admin IDs before deletion
            $adminIds = $project->admins->pluck('id')->toArray();

            // Delete PIC user if exists and was created for this project only
            if ($project->picUser && $project->picUser->adminProjects()->count() <= 1) {
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
                "Project dihapus: {$projectName}",
                null,
                ['deleted_project_name' => $projectName]
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
            $project->id,
            ['old_status' => !$newStatus, 'new_status' => $newStatus]
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

        // Exclude already registered projects
        $registeredCrmIds = Project::whereNotNull('crm_project_id')->pluck('crm_project_id');
        if ($registeredCrmIds->isNotEmpty()) {
            $query->whereNotIn('id', $registeredCrmIds);
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

        // Check if already registered
        $existingProject = Project::where('crm_project_id', $id)->first();
        if ($existingProject) {
            return response()->json(['error' => 'Project ini sudah terdaftar'], 409);
        }

        return response()->json([
            'id' => $project->id,
            'name' => $project->nama_project,
            'description' => $project->deskripsi ?? null,
            'location' => $project->lokasi ?? null,
        ]);
    }

    /**
     * Get project statistics for dashboard
     */
    public function getProjectStatistics()
    {
        return response()->json([
            'total_projects' => Project::count(),
            'active_projects' => Project::where('is_active', true)->count(),
            'internal_projects' => Project::where('registration_type', 'internal')->count(),
            'manual_projects' => Project::where('registration_type', 'manual')->count(),
            'crm_projects' => Project::where('registration_type', 'crm')->count(),
            'pending_registrations' => Project::where('registration_status', 'pending')->count(),
            'approved_projects' => Project::where('registration_status', 'approved')->count(),
            'rejected_projects' => Project::where('registration_status', 'rejected')->count(),
            'projects_with_units' => Project::has('units')->count(),
            'projects_with_affiliators' => Project::has('affiliatorProjects')->count(),
            'projects_with_leads' => Project::has('leads')->count(),
        ]);
    }

    /**
     * Export projects to CSV
     */
    public function exportProjects(Request $request)
    {
        $projects = Project::with(['admins', 'picUser', 'latestRegistration.submittedBy'])
            ->when($request->registration_type, function ($query) use ($request) {
                $query->where('registration_type', $request->registration_type);
            })
            ->when($request->registration_status, function ($query) use ($request) {
                $query->where('registration_status', $request->registration_status);
            })
            ->when($request->is_active !== null, function ($query) use ($request) {
                $query->where('is_active', $request->boolean('is_active'));
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $csvData = [];
        $csvData[] = [
            'ID',
            'Nama Project',
            'Developer',
            'Lokasi',
            'Tipe Registration',
            'Status Registration',
            'Status Aktif',
            'PIC Name',
            'PIC Email',
            'Total Admin',
            'Total Unit',
            'Total Affiliator',
            'Total Lead',
            'Tanggal Dibuat',
            'Tanggal Disetujui'
        ];

        foreach ($projects as $project) {
            $csvData[] = [
                $project->id,
                $project->name,
                $project->developer_name ?: '-',
                $project->location ?: '-',
                $project->registration_type_label,
                $project->registration_status_label,
                $project->is_active ? 'Aktif' : 'Tidak Aktif',
                $project->pic_name ?: '-',
                $project->pic_email ?: '-',
                $project->admins->count(),
                $project->units->count(),
                $project->affiliatorProjects->count(),
                $project->leads->count(),
                $project->created_at->format('Y-m-d H:i:s'),
                $project->approved_at ? $project->approved_at->format('Y-m-d H:i:s') : '-'
            ];
        }

        $filename = 'projects_export_' . now()->format('Y_m_d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($csvData) {
            $file = fopen('php://output', 'w');
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, 200, $headers);
    }

    /**
     * Duplicate project
     */
    public function duplicateProject(Project $project)
    {
        try {
            $duplicatedProject = DB::transaction(function () use ($project) {
                // Create duplicate project
                $newProject = $project->replicate();
                $newProject->name = $project->name . ' (Copy)';
                $newProject->slug = null; // Will be auto-generated
                $newProject->is_active = false;
                $newProject->created_at = now();
                $newProject->updated_at = now();
                $newProject->approved_at = now();
                $newProject->approved_by = Auth::id();
                
                // Reset PIC info for duplicate
                $newProject->pic_user_id = null;
                
                // Copy files if they exist
                if ($project->logo) {
                    $newLogoPath = str_replace('logos/', 'logos/copy_', $project->logo);
                    if (Storage::disk('public')->exists($project->logo)) {
                        Storage::disk('public')->copy($project->logo, $newLogoPath);
                        $newProject->logo = $newLogoPath;
                    }
                }
                
                if ($project->brochure_file) {
                    $newBrochurePath = str_replace('brochures/', 'brochures/copy_', $project->brochure_file);
                    if (Storage::disk('public')->exists($project->brochure_file)) {
                        Storage::disk('public')->copy($project->brochure_file, $newBrochurePath);
                        $newProject->brochure_file = $newBrochurePath;
                    }
                }
                
                if ($project->price_list_file) {
                    $newPriceListPath = str_replace('price_lists/', 'price_lists/copy_', $project->price_list_file);
                    if (Storage::disk('public')->exists($project->price_list_file)) {
                        Storage::disk('public')->copy($project->price_list_file, $newPriceListPath);
                        $newProject->price_list_file = $newPriceListPath;
                    }
                }
                
                $newProject->save();
                
                // Duplicate units
                foreach ($project->units as $unit) {
                    $newUnit = $unit->replicate();
                    $newUnit->project_id = $newProject->id;
                    $newUnit->is_active = false;
                    $newUnit->crm_unit_id = null; // Reset CRM connection
                    $newUnit->save();
                }
                
                return $newProject;
            });

            // Log activity
            $this->activityLogService->log(
                Auth::id(),
                'duplicate_project',
                "Project diduplikasi: {$project->name} -> {$duplicatedProject->name}",
                $duplicatedProject->id,
                ['original_project_id' => $project->id]
            );

            return redirect()->route('superadmin.projects.edit', $duplicatedProject)
                ->with('success', 'Project berhasil diduplikasi! Silakan edit sesuai kebutuhan.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menduplikasi project: ' . $e->getMessage()]);
        }
    }

    /**
     * Get project performance metrics
     */
    public function getProjectMetrics(Project $project, Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        $metrics = [
            'affiliators' => [
                'total' => $project->affiliatorProjects()->count(),
                'active' => $project->affiliatorProjects()->where('status', 'active')->count(),
                'pending' => $project->affiliatorProjects()->where('verification_status', 'pending')->count(),
                'new_this_period' => $project->affiliatorProjects()->where('created_at', '>=', $startDate)->count(),
            ],
            'leads' => [
                'total' => $project->leads()->count(),
                'verified' => $project->leads()->verified()->count(),
                'pending' => $project->leads()->where('verification_status', 'pending')->count(),
                'new_this_period' => $project->leads()->where('created_at', '>=', $startDate)->count(),
            ],
            'commission' => [
                'total_earned' => $project->commissionHistories()->where('type', 'earned')->sum('amount'),
                'total_withdrawn' => $project->commissionHistories()->where('type', 'withdrawn')->sum('amount'),
                'this_period' => $project->commissionHistories()
                    ->where('type', 'earned')
                    ->where('created_at', '>=', $startDate)
                    ->sum('amount'),
            ],
            'units' => [
                'total' => $project->units()->count(),
                'active' => $project->units()->active()->count(),
                'with_leads' => $project->units()->has('leads')->count(),
            ]
        ];

        return response()->json($metrics);
    }
}