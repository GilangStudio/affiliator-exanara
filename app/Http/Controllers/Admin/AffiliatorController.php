<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\AffiliatorProject;
use App\Models\Lead;
use App\Models\CommissionHistory;
use App\Models\CommissionWithdrawal;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use App\Services\CommissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AffiliatorController extends Controller
{
    protected $activityLogService;
    protected $notificationService;
    protected $commissionService;

    public function __construct(
        ActivityLogService $activityLogService,
        NotificationService $notificationService,
        CommissionService $commissionService
    ) {
        $this->activityLogService = $activityLogService;
        $this->notificationService = $notificationService;
        $this->commissionService = $commissionService;
    }

    /**
     * Display a listing of affiliators for admin's projects
     */
    public function index(Request $request)
    {
        $admin = User::find(Auth::user()->id);
        
        // Get projects that this admin manages
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        
        if ($adminProjects->isEmpty()) {
            return view('pages.admin.affiliators.index', [
                'affiliators' => collect(),
                'projects' => collect()
            ]);
        }
        
        // Get all projects for filter dropdown
        $projects = Project::whereIn('id', $adminProjects)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
        
        // Base query for affiliators who have projects managed by this admin
        $query = User::affiliators()
            ->whereHas('affiliatorProjects', function($q) use ($adminProjects) {
                $q->whereIn('project_id', $adminProjects);
            })
            ->with([
                'affiliatorProjects' => function($q) use ($adminProjects) {
                    $q->whereIn('project_id', $adminProjects)->with('project');
                }
            ]);
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        if ($request->filled('verification')) {
            $query->whereHas('affiliatorProjects', function($q) use ($request, $adminProjects) {
                $q->where('verification_status', $request->verification)
                  ->whereIn('project_id', $adminProjects);
            });
        }
        
        if ($request->filled('project')) {
            $query->whereHas('affiliatorProjects', function($q) use ($request) {
                $q->where('project_id', $request->project);
            });
        }
        
        // Get affiliators with pagination
        $affiliators = $query->latest()->paginate(15);
        
        // Add computed properties for each affiliator
        $affiliators->getCollection()->transform(function ($affiliator) {
            // Get leads through affiliator projects
            $affiliator->leads = Lead::whereHas('affiliatorProject', function($q) use ($affiliator) {
                $q->where('user_id', $affiliator->id);
            })->get();
            
            // Get commissions
            $affiliator->commissions = CommissionHistory::where('user_id', $affiliator->id)->get();
            
            // Get withdrawals
            $affiliator->withdrawals = CommissionWithdrawal::where('user_id', $affiliator->id)->get();
            
            return $affiliator;
        });
        
        return view('pages.admin.affiliators.index', compact('affiliators', 'projects'));
    }
    
    /**
     * Display the specified affiliator
     */
    public function show(User $affiliator)
    {
        $admin = User::find(Auth::user()->id);
        
        // Check if admin can access this affiliator
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        $affiliatorProject = $affiliator->affiliatorProjects()
            ->whereIn('project_id', $adminProjects)
            ->with('project')
            ->first();
        
        if (!$affiliatorProject) {
            abort(403, 'Anda tidak memiliki akses ke affiliator ini.');
        }
        
        // Get stats using existing service methods
        $commissionStats = $this->commissionService->getUserCommissionStats($affiliator);
        
        // Get leads
        $totalLeads = Lead::whereHas('affiliatorProject', function($q) use ($affiliator) {
            $q->where('user_id', $affiliator->id);
        })->count();
        
        $verifiedLeads = Lead::whereHas('affiliatorProject', function($q) use ($affiliator) {
            $q->where('user_id', $affiliator->id);
        })->verified()->count();
        
        $stats = [
            'total_leads' => $totalLeads,
            'verified_leads' => $verifiedLeads,
            'total_commission' => $commissionStats['total_earned'],
            'monthly_commission' => $commissionStats['this_month_earned'],
            'available_balance' => $commissionStats['available_balance'],
            'withdrawn_amount' => $commissionStats['total_withdrawn'],
            'conversion_rate' => $totalLeads > 0 ? ($verifiedLeads / $totalLeads) * 100 : 0
        ];
        
        // Get recent data
        $leads = Lead::whereHas('affiliatorProject', function($q) use ($affiliator) {
            $q->where('user_id', $affiliator->id);
        })->with('affiliatorProject.project')->latest()->limit(10)->get();
        
        $commissions = CommissionHistory::where('user_id', $affiliator->id)
            ->with(['lead', 'project'])
            ->latest()
            ->limit(10)
            ->get();
        
        $withdrawals = CommissionWithdrawal::where('user_id', $affiliator->id)
            ->with('bankAccount')
            ->latest()
            ->limit(10)
            ->get();
        
        $activities = ActivityLog::where('user_id', $affiliator->id)
            ->orWhere(function($q) use ($affiliator) {
                $q->whereJsonContains('properties->affiliator_id', $affiliator->id);
            })
            ->with('user')
            ->latest()
            ->limit(15)
            ->get();
        
        return view('pages.admin.affiliators.show', compact(
            'affiliator', 
            'affiliatorProject', 
            'stats', 
            'leads', 
            'commissions', 
            'withdrawals', 
            'activities'
        ));
    }
    
    /**
     * Show the form for editing the specified affiliator
     */
    public function edit(User $affiliator)
    {
        $admin = User::find(Auth::user()->id);
        
        // Check access
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        $affiliatorProject = $affiliator->affiliatorProjects()
            ->whereIn('project_id', $adminProjects)
            ->with('project')
            ->first();
        
        if (!$affiliatorProject) {
            abort(403, 'Anda tidak memiliki akses ke affiliator ini.');
        }
        
        return view('pages.admin.affiliators.edit', compact('affiliator', 'affiliatorProject'));
    }
    
    /**
     * Update the specified affiliator
     */
    public function update(Request $request, User $affiliator)
    {
        $admin = User::find(Auth::user()->id);
        
        // Check access
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        $affiliatorProject = $affiliator->affiliatorProjects()
            ->whereIn('project_id', $adminProjects)
            ->first();
        
        if (!$affiliatorProject) {
            abort(403, 'Anda tidak memiliki akses ke affiliator ini.');
        }
        
        // Validation using existing pattern
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($affiliator->id)
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{9,13}$/',
                Rule::unique('users', 'phone')->ignore($affiliator->id)
            ],
            'password' => 'nullable|min:8|confirmed',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'country_code' => 'required|string|max:5'
        ];
        
        $validatedData = $request->validate($rules, [
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format: 8xxxxxxxxx (tanpa +62 dan tanpa 0 di depan)',
        ]);
        
        try {
            DB::beginTransaction();
            
            $oldData = $affiliator->only(['name', 'email', 'phone', 'is_active']);
            
            // Handle profile photo upload using existing pattern
            if ($request->hasFile('profile_photo')) {
                if ($affiliator->profile_photo && Storage::disk('public')->exists($affiliator->profile_photo)) {
                    Storage::disk('public')->delete($affiliator->profile_photo);
                }
                $validatedData['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
            }
            
            // Handle password update
            if ($request->filled('password')) {
                $validatedData['password'] = Hash::make($request->password);
            } else {
                unset($validatedData['password']);
            }
            
            $validatedData['is_active'] = $request->has('is_active');
            
            // Update affiliator
            $affiliator->update($validatedData);
            
            // Log activity using service
            $this->activityLogService->logProfileActivity(
                $admin->id,
                'update_affiliator_profile',
                "Admin memperbarui profil affiliator {$affiliator->name}",
                [
                    'affiliator_id' => $affiliator->id,
                    'old_data' => $oldData,
                    'new_data' => $affiliator->only(['name', 'email', 'phone', 'is_active'])
                ]
            );
            
            // Send notification if status changed
            if ($oldData['is_active'] !== $affiliator->is_active) {
                $this->notificationService->sendAccountStatusNotification(
                    $affiliator->id,
                    $affiliator->is_active ? 'activated' : 'deactivated'
                );
            }
            
            DB::commit();
            
            return redirect()
                ->route('admin.affiliators.show', $affiliator)
                ->with('success', 'Profile affiliator berhasil diperbarui.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui profile: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle affiliator status
     */
    public function toggleStatus(User $affiliator)
    {
        $admin = User::find(Auth::user()->id);
        
        // Check access
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        if (!$affiliator->affiliatorProjects()->whereIn('project_id', $adminProjects)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke affiliator ini.');
        }
        
        try {
            $newStatus = !$affiliator->is_active;
            $affiliator->update(['is_active' => $newStatus]);
            
            // Log using service
            $this->activityLogService->logProfileActivity(
                $admin->id,
                'toggle_affiliator_status',
                "Admin " . ($newStatus ? 'mengaktifkan' : 'menonaktifkan') . " affiliator {$affiliator->name}",
                ['affiliator_id' => $affiliator->id, 'new_status' => $newStatus]
            );
            
            // Send notification
            $this->notificationService->sendAccountStatusNotification(
                $affiliator->id,
                $newStatus ? 'activated' : 'deactivated'
            );
            
            $message = $newStatus ? 'Affiliator berhasil diaktifkan.' : 'Affiliator berhasil dinonaktifkan.';
            
            return redirect()->back()->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengubah status: ' . $e->getMessage());
        }
    }
    
    /**
     * Verify affiliator using existing AffiliatorProject structure
     */
    public function verify(Request $request, User $affiliator)
    {
        $admin = User::find(Auth::user()->id);
        
        $request->validate([
            'verification_status' => 'required|in:verified,rejected',
            'verification_notes' => 'nullable|string|max:500',
            'project_id' => 'required|exists:projects,id'
        ]);
        
        // Check access
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        if (!$adminProjects->contains($request->project_id)) {
            abort(403, 'Anda tidak memiliki akses ke project ini.');
        }
        
        $affiliatorProject = AffiliatorProject::where('user_id', $affiliator->id)
            ->where('project_id', $request->project_id)
            ->first();
        
        if (!$affiliatorProject) {
            abort(404, 'Affiliator project tidak ditemukan.');
        }
        
        try {
            DB::beginTransaction();
            
            // Update verification using existing structure
            $affiliatorProject->update([
                'verification_status' => $request->verification_status,
                'verification_notes' => $request->verification_notes,
                'verified_at' => $request->verification_status === 'verified' ? now() : null,
                'verified_by' => $admin->id
            ]);
            
            // Log using service
            $this->activityLogService->logAffiliatorProjectActivity(
                $admin->id,
                'verify_affiliator',
                $affiliatorProject->project_id,
                "Admin " . ($request->verification_status === 'verified' ? 'memverifikasi' : 'menolak') . 
                " affiliator {$affiliator->name}",
                [
                    'affiliator_id' => $affiliator->id,
                    'verification_status' => $request->verification_status,
                    'verification_notes' => $request->verification_notes
                ]
            );
            
            // Send notification using service
            $this->notificationService->sendKtpVerificationNotification(
                $affiliatorProject->id,
                $request->verification_status,
                $request->verification_notes
            );
            
            DB::commit();
            
            $message = $request->verification_status === 'verified' 
                ? 'Affiliator berhasil diverifikasi.' 
                : 'Verifikasi affiliator berhasil ditolak.';
            
            return redirect()->back()->with('success', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memverifikasi: ' . $e->getMessage());
        }
    }
    
    /**
     * Reset affiliator password
     */
    public function resetPassword(Request $request, User $affiliator)
    {
        $admin = User::find(Auth::user()->id);
        
        // Check access
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        if (!$affiliator->affiliatorProjects()->whereIn('project_id', $adminProjects)->exists()) {
            abort(403, 'Anda tidak memiliki akses ke affiliator ini.');
        }
        
        $request->validate([
            'new_password' => 'required|min:8|confirmed'
        ]);
        
        try {
            $affiliator->update([
                'password' => Hash::make($request->new_password)
            ]);
            
            // Log using service
            $this->activityLogService->logAuth(
                $admin->id,
                'reset_affiliator_password',
                "Admin mereset password affiliator {$affiliator->name}"
            );
            
            // Send notification
            $this->notificationService->createForUser(
                $affiliator->id,
                'Password Direset',
                'Password akun Anda telah direset oleh Admin. Silakan login dengan password baru.',
                'warning'
            );
            
            return redirect()->back()->with('success', 'Password berhasil direset.');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mereset password: ' . $e->getMessage());
        }
    }
    
    /**
     * Export affiliators data
     */
    public function export(Request $request)
    {
        $admin = User::find(Auth::user()->id);
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        
        if ($adminProjects->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor.');
        }
        
        // Apply same filters as index
        $query = User::affiliators()
            ->whereHas('affiliatorProjects', function($q) use ($adminProjects) {
                $q->whereIn('project_id', $adminProjects);
            })
            ->with('affiliatorProjects.project');
        
        // Apply filters...
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $affiliators = $query->get();
        
        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'Nama', 'Email', 'Nomor HP', 'Project', 'Status', 'Status Verifikasi',
            'Total Lead', 'Lead Terverifikasi', 'Total Komisi', 'Komisi Ditarik', 'Bergabung'
        ];
        
        foreach ($affiliators as $affiliator) {
            $affiliatorProject = $affiliator->affiliatorProjects->first();
            
            // Get stats using existing methods
            $totalLeads = Lead::whereHas('affiliatorProject', function($q) use ($affiliator) {
                $q->where('user_id', $affiliator->id);
            })->count();
            
            $verifiedLeads = Lead::whereHas('affiliatorProject', function($q) use ($affiliator) {
                $q->where('user_id', $affiliator->id);
            })->verified()->count();
            
            $commissionStats = $this->commissionService->getUserCommissionStats($affiliator);
            
            $csvData[] = [
                $affiliator->name,
                $affiliator->email,
                $affiliator->country_code . ' ' . $affiliator->phone,
                $affiliatorProject ? $affiliatorProject->project->name : '-',
                $affiliator->is_active ? 'Aktif' : 'Tidak Aktif',
                $affiliatorProject ? ucfirst($affiliatorProject->verification_status) : '-',
                $totalLeads,
                $verifiedLeads,
                'Rp ' . number_format($commissionStats['total_earned'], 0, ',', '.'),
                'Rp ' . number_format($commissionStats['total_withdrawn'], 0, ',', '.'),
                $affiliator->created_at->format('d/m/Y H:i')
            ];
        }
        
        // Generate CSV
        $filename = 'affiliators_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $handle = fopen('php://output', 'w');
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        // Add BOM for UTF-8
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
        
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        
        fclose($handle);
        exit;
    }
}