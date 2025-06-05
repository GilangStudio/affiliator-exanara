<?php

namespace App\Http\Controllers\Affiliator;

use App\Models\Unit;
use App\Models\User;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\AffiliatorProject;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Auth;
use App\Services\NotificationService;
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
        // Only show projects that are active AND have at least one active unit
        $availableProjects = Project::active()
            ->whereNotIn('id', $joinedProjectIds)
            ->whereHas('units', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['units' => function ($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get()
            ->map(function ($project) {
                $commissionInfo = $this->calculateCommissionInfoFromUnits($project->units);
                
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'slug' => $project->slug,
                    'location' => $project->location,
                    'description' => $project->description,
                    'logo_url' => $project->logo_url ?: asset('images/default-project.png'),
                    'terms_and_conditions' => $project->terms_and_conditions,
                    'additional_info' => $project->additional_info,
                    'require_digital_signature' => $project->require_digital_signature,
                    'is_active' => $project->is_active,
                    'created_at_formatted' => $project->created_at->format('d M Y'),
                    'units_count' => $project->units->count(),
                    'commission_preview' => $commissionInfo['range'],
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
     */
    public function getAvailableProjects()
    {
        $user = User::findOrFail(Auth::user()->id);
        $joinedProjectIds = $user->affiliatorProjects()->pluck('project_id');
        
        // Only show projects that are active AND have at least one active unit
        $projects = Project::active()
            ->whereNotIn('id', $joinedProjectIds)
            ->whereHas('units', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['units' => function ($query) {
                $query->where('is_active', true);
            }])
            ->select('id', 'name', 'description', 'logo', 'location')
            ->get()
            ->map(function($project) {
                $commissionInfo = $this->calculateCommissionInfoFromUnits($project->units);
                
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'location' => $project->location,
                    'logo_url' => $project->logo_url,
                    'units_count' => $project->units->count(),
                    'commission_preview' => $commissionInfo['range'],
                ];
            });
        
        return response()->json($projects);
    }

    /**
     * Check if user can join more projects
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
     */
    public function validateProject(Request $request)
    {
        $request->validate(['project_id' => 'required|exists:projects,id']);
        
        $user = User::findOrFail(Auth::user()->id);
        $project = Project::findOrFail($request->project_id);
        
        // Check if project is active
        if (!$project->is_active) {
            return response()->json([
                'valid' => false,
                'message' => 'Project ini sedang tidak aktif'
            ]);
        }
        
        // Check if project has active units
        $hasActiveUnits = $project->units()->where('is_active', true)->exists();
        if (!$hasActiveUnits) {
            return response()->json([
                'valid' => false,
                'message' => 'Project ini belum memiliki unit yang tersedia'
            ]);
        }
        
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
        
        // Check if project is active
        if (!$project->is_active) {
            return response()->json([
                'error' => 'Project ini sedang tidak aktif',
                'status' => 'inactive'
            ], 400);
        }
        
        // Check if project has active units
        $hasActiveUnits = $project->units()->where('is_active', true)->exists();
        if (!$hasActiveUnits) {
            return response()->json([
                'error' => 'Project ini belum memiliki unit yang tersedia',
                'status' => 'no_units'
            ], 400);
        }
        
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

        // Get active units for this project
        $units = Unit::where('project_id', $project->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'name' => $unit->name,
                    'description' => Str::limit($unit->description, 100),
                    'price_formatted' => $unit->price_formatted,
                    'commission_display' => $unit->commission_display,
                    'commission_type' => $unit->commission_type,
                    'commission_value' => $unit->commission_value,
                    'unit_specs' => $unit->unit_specs,
                    'image' => $unit->image_url,
                    'specs' => $unit->unit_specs,
                    'unit_type_display' => $unit->unit_type_display,
                    'building_area_formatted' => $unit->building_area_formatted,
                    'land_area_formatted' => $unit->land_area_formatted,
                ];
            });

        // Calculate commission range
        $commissionInfo = $this->calculateCommissionInfoFromUnits($units);

        // Return detailed project information
        return response()->json([
            'id' => $project->id,
            'name' => $project->name,
            'slug' => $project->slug,
            'description' => $project->description,
            'location' => $project->location,
            'logo_url' => $project->logo_url,
            'commission_range' => $commissionInfo['range'],
            'commission_description' => $commissionInfo['description'],
            'terms_and_conditions' => $project->terms_and_conditions,
            'additional_info' => $project->additional_info,
            'units' => $units,
            'units_count' => $units->count(),
            'require_digital_signature' => $project->require_digital_signature,
        ]);
    }

    /**
     * Calculate commission information from units
     */
    private function calculateCommissionInfoFromUnits($units)
    {
        if ($units->isEmpty()) {
            return [
                'range' => 'Komisi Tidak Tersedia',
                'description' => 'Belum ada unit yang tersedia untuk project ini'
            ];
        }

        $percentageUnits = $units->where('commission_type', 'percentage');
        $fixedUnits = $units->where('commission_type', 'fixed');

        $ranges = [];
        $descriptions = [];

        if ($percentageUnits->count() > 0) {
            $minPercentage = $percentageUnits->min('commission_value');
            $maxPercentage = $percentageUnits->max('commission_value');
            
            if ($minPercentage == $maxPercentage) {
                $ranges[] = number_format($minPercentage, 1) . '%';
            } else {
                $ranges[] = number_format($minPercentage, 1) . '% - ' . number_format($maxPercentage, 1) . '%';
            }
            
            $descriptions[] = 'komisi persentase dari harga unit';
        }

        if ($fixedUnits->count() > 0) {
            $minFixed = $fixedUnits->min('commission_value');
            $maxFixed = $fixedUnits->max('commission_value');
            
            if ($minFixed == $maxFixed) {
                $ranges[] = 'Rp ' . number_format($minFixed, 0, ',', '.');
            } else {
                $ranges[] = 'Rp ' . number_format($minFixed, 0, ',', '.') . ' - Rp ' . number_format($maxFixed, 0, ',', '.');
            }
            
            $descriptions[] = 'komisi tetap per unit';
        }

        return [
            'range' => implode(' atau ', $ranges),
            'description' => 'Anda akan mendapat ' . implode(' atau ', $descriptions) . ' tergantung unit yang dipilih customer'
        ];
    }

    /**
     * Join a project with digital signature
     */
    public function joinProject(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'ktp_number' => 'required|string|size:16',
            'ktp_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            // 'digital_signature' => 'required|string',
            'terms_accepted' => 'required|accepted'
        ], [
            'project_id.required' => 'Pilih project terlebih dahulu',
            'project_id.exists' => 'Project tidak valid',
            'ktp_number.required' => 'Nomor KTP harus diisi',
            'ktp_number.size' => 'Nomor KTP harus 16 digit',
            'ktp_photo.required' => 'Foto KTP harus diupload',
            'ktp_photo.image' => 'File harus berupa gambar',
            'ktp_photo.mimes' => 'Format gambar harus JPEG, PNG, atau JPG',
            'ktp_photo.max' => 'Ukuran file maksimal 2MB',
            // 'digital_signature.required' => 'Tanda tangan digital diperlukan',
            'terms_accepted.required' => 'Anda harus menyetujui syarat & ketentuan',
            'terms_accepted.accepted' => 'Anda harus menyetujui syarat & ketentuan'
        ]);

        $user = User::findOrFail(Auth::user()->id);
        $project = Project::findOrFail($request->project_id);

        //validation for digital signature if project require digital signature
        if ($project->require_digital_signature) {
            $request->validate([
                //validate digital signature must be svg format <svg></svg>
                'digital_signature' =>[
                    'required',
                    'string',
                    function ($attribute, $value, $fail) {
                        if (strpos($value, '<svg') === false) {
                            $fail('Tanda tangan digital tidak valid');
                        }
                    }
                ],
                
            ], [
                'digital_signature.required' => 'Tanda tangan digital diperlukan',
            ]);

            
        }


        // Additional validation
        $existingProject = $user->affiliatorProjects()
            ->where('project_id', $project->id)
            ->first();

        if ($existingProject) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah bergabung dengan project ini'
            ], 400);
        }

        // Check if project is active
        if (!$project->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Project ini sedang tidak aktif'
            ], 400);
        }

        // Check if project has active units
        $hasActiveUnits = $project->units()->where('is_active', true)->exists();
        if (!$hasActiveUnits) {
            return response()->json([
                'success' => false,
                'message' => 'Project ini belum memiliki unit yang tersedia'
            ], 400);
        }

        // Check max projects limit
        $maxProjects = \App\Models\SystemSetting::getValue('max_projects_per_affiliator', 3);
        $currentCount = $user->affiliatorProjects()->count();
        
        if ($currentCount >= $maxProjects) {
            return response()->json([
                'success' => false,
                'message' => "Anda sudah mencapai batas maksimal {$maxProjects} project"
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Upload KTP photo
            $ktpPhotoPath = null;
            if ($request->hasFile('ktp_photo')) {
                $ktpPhotoPath = $request->file('ktp_photo')->store('ktp_photos', 'public');
            }

            // Create affiliator project with initial data
            $affiliatorProject = AffiliatorProject::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'ktp_number' => $request->ktp_number,
                'ktp_photo' => $ktpPhotoPath,
                'verification_status' => 'pending',
                'status' => 'pending_verification',
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
                    'has_ktp' => true,
                    'terms_accepted' => true,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            );

            // Notify project admins
            $this->notificationService->notifyProjectAdmins(
                $project->id,
                'Affiliator Baru Bergabung',
                "Affiliator {$user->name} telah bergabung dengan project {$project->name} dan menunggu verifikasi KTP",
                'info',
                [
                    'affiliator_id' => $user->id,
                    'affiliator_name' => $user->name,
                    'project_id' => $project->id,
                    'action_url' => route('admin.affiliators.show', $affiliatorProject->id)
                ]
            );

            // Send notification to user
            $this->notificationService->createForUser(
                $user->id,
                'Berhasil Bergabung!',
                "Anda telah berhasil bergabung dengan project {$project->name}. KTP Anda sedang dalam proses verifikasi oleh admin.",
                'success',
                [
                    'project_id' => $project->id,
                    'affiliator_project_id' => $affiliatorProject->id
                ]
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil bergabung dengan project {$project->name}! KTP Anda sedang dalam proses verifikasi.",
                'next_step' => 'verification_pending',
                'redirect_url' => route('affiliator.dashboard')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            // Delete uploaded file if exists
            if ($ktpPhotoPath && Storage::disk('public')->exists($ktpPhotoPath)) {
                Storage::disk('public')->delete($ktpPhotoPath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal bergabung dengan project: ' . $e->getMessage()
            ], 500);
        }
    }
}