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
     * Display available projects to join with pagination and filters
     */
    public function index(Request $request)
    {
        $user = User::find(Auth::user()->id);
        
        // Get joined project IDs
        $joinedProjectIds = $user->affiliatorProjects()->pluck('project_id');

        // **TAMBAHAN**: Cek parameter project dari URL atau session
        $autoSelectProjectSlug = $request->get('project') ?? session('auto_select_project');
        
        // Build query for available projects
        $query = Project::active()
            ->whereNotIn('id', $joinedProjectIds)
            ->whereHas('units', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['units' => function ($query) {
                $query->where('is_active', true);
            }]);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('developer_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply location filter
        if ($request->filled('location')) {
            $location = $request->get('location');
            $query->where('location', 'like', "%{$location}%");
        }

        // Apply commission filter
        if ($request->filled('commission_type')) {
            $commissionType = $request->get('commission_type');
            $query->whereHas('units', function($q) use ($commissionType) {
                $q->where('commission_type', $commissionType);
            });
        }

        // Apply commission range filter
        if ($request->filled('min_commission') || $request->filled('max_commission')) {
            $minCommission = $request->get('min_commission');
            $maxCommission = $request->get('max_commission');
            
            $query->whereHas('units', function($q) use ($minCommission, $maxCommission) {
                if ($minCommission) {
                    $q->where('commission_value', '>=', $minCommission);
                }
                if ($maxCommission) {
                    $q->where('commission_value', '<=', $maxCommission);
                }
            });
        }

        // Apply sorting with auto-select priority
        $sort = $request->get('sort', 'name');
        
        // If there's an auto-select project, prioritize it first
        if ($autoSelectProjectSlug) {
            $query->orderByRaw("CASE WHEN slug = ? THEN 0 ELSE 1 END", [$autoSelectProjectSlug]);
        }
        
        // Then apply regular sorting
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        // Get pagination size
        $perPage = $request->get('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48]) ? $perPage : 12;

        // Get paginated results
        $availableProjects = $query->paginate($perPage)
            ->through(function ($project) {
                $commissionInfo = $this->calculateCommissionInfoFromUnits($project->units);
                
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'slug' => $project->slug,
                    'developer_name' => $project->developer_name,
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

        // Get unique locations for filter dropdown
        $locations = Project::active()
            ->whereNotIn('id', $joinedProjectIds)
            ->whereHas('units', function ($query) {
                $query->where('is_active', true);
            })
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->sort();

        // Get commission types for filter
        $commissionTypes = [
            'percentage' => 'Persentase (%)',
            'fixed' => 'Nominal Tetap (Rp)'
        ];

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

        return view('pages.affiliator.join-project', compact(
            'availableProjects', 
            'userProjects', 
            'autoSelectProjectSlug',
            'locations',
            'commissionTypes'
        ));
    }

    /**
     * Get available projects for AJAX with filters
     */
    public function getAvailableProjects(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        $joinedProjectIds = $user->affiliatorProjects()->pluck('project_id');
        
        // Get auto-select project slug from session or request
        $autoSelectProjectSlug = $request->get('auto_select_project') ?? session('auto_select_project');
        
        // Build query
        $query = Project::active()
            ->whereNotIn('id', $joinedProjectIds)
            ->whereHas('units', function ($query) {
                $query->where('is_active', true);
            })
            ->with(['units' => function ($query) {
                $query->where('is_active', true);
            }]);

        // Apply filters (same as index method)
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('developer_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location')) {
            $location = $request->get('location');
            $query->where('location', 'like', "%{$location}%");
        }

        if ($request->filled('commission_type')) {
            $commissionType = $request->get('commission_type');
            $query->whereHas('units', function($q) use ($commissionType) {
                $q->where('commission_type', $commissionType);
            });
        }

        if ($request->filled('min_commission') || $request->filled('max_commission')) {
            $minCommission = $request->get('min_commission');
            $maxCommission = $request->get('max_commission');
            
            $query->whereHas('units', function($q) use ($minCommission, $maxCommission) {
                if ($minCommission) {
                    $q->where('commission_value', '>=', $minCommission);
                }
                if ($maxCommission) {
                    $q->where('commission_value', '<=', $maxCommission);
                }
            });
        }

        // Apply sorting with auto-select priority
        $sort = $request->get('sort', 'name');
        
        // If there's an auto-select project, prioritize it first
        if ($autoSelectProjectSlug) {
            $query->orderByRaw("CASE WHEN slug = ? THEN 0 ELSE 1 END", [$autoSelectProjectSlug]);
        }
        
        // Then apply regular sorting
        switch ($sort) {
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        // Get pagination size
        $perPage = $request->get('per_page', 12);
        $perPage = in_array($perPage, [12, 24, 48]) ? $perPage : 12;

        $projects = $query->paginate($perPage)
            ->through(function($project) {
                $commissionInfo = $this->calculateCommissionInfoFromUnits($project->units);
                
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'slug' => $project->slug,
                    'developer_name' => $project->developer_name,
                    'description' => $project->description,
                    'location' => $project->location,
                    'logo_url' => $project->logo_url,
                    'units_count' => $project->units->count(),
                    'commission_preview' => $commissionInfo['range'],
                    'require_digital_signature' => $project->require_digital_signature,
                ];
            });
        
        // If AJAX request, return JSON with pagination data
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'data' => $projects->items(),
                'pagination' => [
                    'current_page' => $projects->currentPage(),
                    'last_page' => $projects->lastPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'from' => $projects->firstItem(),
                    'to' => $projects->lastItem(),
                    'has_more_pages' => $projects->hasMorePages(),
                    'on_first_page' => $projects->onFirstPage(),
                ]
            ]);
        }
        
        // If regular HTTP request, return view (for initial page load)
        return $projects;
    }

    /**
     * Get filter options for AJAX
     */
    public function getFilterOptions(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        $joinedProjectIds = $user->affiliatorProjects()->pluck('project_id');
        
        // Get unique locations
        $locations = Project::active()
            ->whereNotIn('id', $joinedProjectIds)
            ->whereHas('units', function ($query) {
                $query->where('is_active', true);
            })
            ->whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->pluck('location')
            ->sort()
            ->values();

        // Get commission ranges
        $commissionRanges = DB::table('units')
            ->join('projects', 'units.project_id', '=', 'projects.id')
            ->where('projects.is_active', true)
            ->where('units.is_active', true)
            ->whereNotIn('projects.id', $joinedProjectIds)
            ->selectRaw('
                MIN(CASE WHEN commission_type = "percentage" THEN commission_value END) as min_percentage,
                MAX(CASE WHEN commission_type = "percentage" THEN commission_value END) as max_percentage,
                MIN(CASE WHEN commission_type = "fixed" THEN commission_value END) as min_fixed,
                MAX(CASE WHEN commission_type = "fixed" THEN commission_value END) as max_fixed
            ')
            ->first();

        return response()->json([
            'locations' => $locations,
            'commission_types' => [
                'percentage' => 'Persentase (%)',
                'fixed' => 'Nominal Tetap (Rp)'
            ],
            'commission_ranges' => [
                'percentage' => [
                    'min' => $commissionRanges->min_percentage ?? 0,
                    'max' => $commissionRanges->max_percentage ?? 100
                ],
                'fixed' => [
                    'min' => $commissionRanges->min_fixed ?? 0,
                    'max' => $commissionRanges->max_fixed ?? 100000000
                ]
            ]
        ]);
    }

    // ... rest of the methods remain the same (no changes to other methods)

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
        $maxAffiliators = \App\Models\SystemSetting::getValue('max_projects_per_affiliator', null);
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

            session()->forget('auto_select_project');

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