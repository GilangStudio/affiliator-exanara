<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Lead;
use App\Models\CommissionHistory;
use App\Models\CommissionWithdrawal;
use App\Models\ActivityLog;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display admin dashboard using existing service
     */
    public function index()
    {
        $admin = User::find(Auth::id());
        
        // Use existing dashboard service
        $data = $this->dashboardService->getDashboardData($admin);
        
        // Get additional data specific for admin
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        
        if ($adminProjects->isEmpty()) {
            $data['recent_leads'] = collect();
            $data['recent_withdrawals'] = collect();
            $data['projects'] = collect();
        } else {
            // Get recent leads
            $data['recent_leads'] = Lead::whereHas('affiliatorProject', function($q) use ($adminProjects) {
                $q->whereIn('project_id', $adminProjects);
            })
            ->with(['affiliatorProject.user', 'affiliatorProject.project'])
            ->latest()
            ->limit(10)
            ->get();
            
            // Get recent withdrawals
            $affiliatorIds = User::whereHas('affiliatorProjects', function($q) use ($adminProjects) {
                $q->whereIn('project_id', $adminProjects);
            })->pluck('id');
            
            $data['recent_withdrawals'] = CommissionWithdrawal::whereIn('user_id', $affiliatorIds)
                ->with('user')
                ->latest()
                ->limit(10)
                ->get();
            
            // Get projects
            $data['projects'] = Project::whereIn('id', $adminProjects)
                ->withCount(['affiliatorProjects', 'leads'])
                ->get();
        }
        
        return view('pages.admin.dashboard.index', $data);
    }

    /**
     * Get stats for AJAX requests
     */
    public function getStats()
    {
        $admin = Auth::user();
        $data = $this->dashboardService->getDashboardData($admin);
        
        return response()->json($data['stats']);
    }
}