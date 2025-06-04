<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\ActivityLogService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
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
     * Show profile settings (like superadmin)
     */
    public function index(Request $request)
    {
        $admin = User::find(Auth::user()->id);
        $activeTab = $request->get('tab', 'profile');
        
        // Get basic stats
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        
        $stats = [
            'total_projects' => $adminProjects->count(),
            'total_activities' => ActivityLog::where('user_id', $admin->id)->count(),
            'login_count' => ActivityLog::where('user_id', $admin->id)
                ->where('action', 'login')
                ->count(),
        ];
        
        $data = [
            'admin' => $admin,
            'activeTab' => $activeTab,
            'stats' => $stats
        ];
        
        // Load data based on active tab
        switch ($activeTab) {
            case 'activity':
                $data = array_merge($data, $this->getActivityData($request, $admin));
                break;
            case 'statistics':
                $data = array_merge($data, $this->getStatisticsData($admin));
                break;
        }
        
        return view('pages.admin.profile.index', $data);
    }

    /**
     * Update admin profile
     */
    public function update(Request $request)
    {
        $admin = User::find(Auth::user()->id);
        
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin->id)
            ],
            'phone' => [
                'required',
                'string',
                'regex:/^[0-9]{9,13}$/',
                Rule::unique('users', 'phone')->ignore($admin->id)
            ],
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
        
        $validatedData = $request->validate($rules, [
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format: 8xxxxxxxxx (tanpa +62 dan tanpa 0 di depan)',
        ]);
        
        try {
            DB::beginTransaction();
            
            $oldData = $admin->only(['name', 'email', 'phone']);
            
            // Handle profile photo
            if ($request->hasFile('profile_photo')) {
                if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                    Storage::disk('public')->delete($admin->profile_photo);
                }
                $validatedData['profile_photo'] = $request->file('profile_photo')->store('profiles', 'public');
            }
            
            // Set country code
            $validatedData['country_code'] = '+62';
            
            $admin->update($validatedData);
            
            // Log activity
            $this->activityLogService->logProfileActivity(
                $admin->id,
                'update_profile',
                'Profil admin diperbarui',
                [
                    'old_data' => $oldData,
                    'new_data' => $admin->only(['name', 'email', 'phone'])
                ]
            );
            
            DB::commit();
            
            return redirect()
                ->route('admin.profile.index', ['tab' => 'profile'])
                ->with('success', 'Profil berhasil diperbarui.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui profil: ' . $e->getMessage());
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $admin = User::find(Auth::user()->id);
        
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        
        // Check current password
        if (!Hash::check($request->current_password, $admin->password)) {
            return redirect()
                ->back()
                ->with('error', 'Password saat ini tidak benar.');
        }
        
        try {
            $admin->update([
                'password' => Hash::make($request->new_password)
            ]);
            
            // Log activity
            $this->activityLogService->logAuth(
                $admin->id,
                'password_change',
                'Admin mengubah password'
            );
            
            // Send notification
            $this->notificationService->createForUser(
                $admin->id,
                'Password Diubah',
                'Password akun admin Anda berhasil diubah.',
                'info'
            );
            
            return redirect()
                ->route('admin.profile.index', ['tab' => 'password'])
                ->with('success', 'Password berhasil diubah.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal mengubah password: ' . $e->getMessage());
        }
    }

    /**
     * Delete profile photo
     */
    public function deletePhoto()
    {
        $admin = User::find(Auth::user()->id);
        
        try {
            if ($admin->profile_photo && Storage::disk('public')->exists($admin->profile_photo)) {
                Storage::disk('public')->delete($admin->profile_photo);
                $admin->update(['profile_photo' => null]);
                
                // Log activity
                $this->activityLogService->logProfileActivity(
                    $admin->id,
                    'delete_profile_photo',
                    'Foto profil admin dihapus'
                );
                
                return redirect()
                    ->back()
                    ->with('success', 'Foto profil berhasil dihapus.');
            }
            
            return redirect()
                ->back()
                ->with('info', 'Tidak ada foto profil untuk dihapus.');
                
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Gagal menghapus foto: ' . $e->getMessage());
        }
    }

    /**
     * Get activity data for activity tab
     */
    private function getActivityData(Request $request, User $admin)
    {
        $query = ActivityLog::where('user_id', $admin->id)
            ->with('project')
            ->orderBy('created_at', 'desc');
        
        // Apply filters
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $activities = $query->paginate(15);
        
        // Get available actions for filter
        $available_actions = ActivityLog::where('user_id', $admin->id)
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();
        
        return [
            'activities' => $activities,
            'available_actions' => $available_actions
        ];
    }

    /**
     * Get statistics data for statistics tab
     */
    private function getStatisticsData(User $admin)
    {
        // Activity stats
        $activityStats = [
            'total_activities' => ActivityLog::where('user_id', $admin->id)->count(),
            'this_month' => ActivityLog::where('user_id', $admin->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'this_week' => ActivityLog::where('user_id', $admin->id)
                ->where('created_at', '>=', now()->startOfWeek())
                ->count(),
            'today' => ActivityLog::where('user_id', $admin->id)
                ->whereDate('created_at', today())
                ->count()
        ];
        
        // Activity by action
        $activityByAction = ActivityLog::where('user_id', $admin->id)
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();
        
        // Monthly activity for chart
        $monthlyActivity = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = ActivityLog::where('user_id', $admin->id)
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            
            $monthlyActivity[] = [
                'month' => $date->format('M Y'),
                'count' => $count
            ];
        }
        
        return [
            'activityStats' => $activityStats,
            'activityByAction' => $activityByAction,
            'monthlyActivity' => $monthlyActivity
        ];
    }
}