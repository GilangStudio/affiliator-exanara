<?php 

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Project;
use App\Models\Unit;
use App\Models\Lead;
use App\Models\CommissionHistory;
use App\Models\CommissionWithdrawal;
use App\Models\ActivityLog;
use App\Models\AffiliatorProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $admin = User::find(Auth::id());
        
        // Get projects that this admin manages
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        
        if ($adminProjects->isEmpty()) {
            return view('pages.admin.dashboard.index', [
                'stats' => [],
                'projects' => collect(),
                'recent_leads' => collect(),
                'recent_withdrawals' => collect(),
                'recent_activities' => collect(),
                'monthly_stats' => []
            ]);
        }

        // Get units from admin's projects (for commission calculation)
        $adminUnits = Unit::whereIn('project_id', $adminProjects)->pluck('id');
        
        // Basic stats
        $stats = [
            'total_projects' => $adminProjects->count(),
            'total_affiliators' => AffiliatorProject::whereIn('project_id', $adminProjects)
                ->distinct('user_id')->count('user_id'),
            'active_affiliators' => AffiliatorProject::whereIn('project_id', $adminProjects)
                ->where('status', 'active')->distinct('user_id')->count('user_id'),
            'total_leads' => Lead::whereIn('unit_id', $adminUnits)->count(),
            'verified_leads' => Lead::whereIn('unit_id', $adminUnits)->verified()->count(),
            'pending_leads' => Lead::whereIn('unit_id', $adminUnits)
                ->where('verification_status', 'pending')->count(),
            'total_commission' => CommissionHistory::whereIn('unit_id', $adminUnits)
                ->where('type', 'earned')->sum('amount'),
            'pending_withdrawals' => CommissionWithdrawal::whereIn('project_id', $adminProjects)
                ->where('status', 'pending')->count(),
        ];

        // Get projects with counts
        $projects = Project::whereIn('id', $adminProjects)
            ->withCount([
                'affiliatorProjects',
                'leads' => function($query) {
                    $query->whereHas('unit');
                }
            ])
            ->get();

        // Recent leads
        $recent_leads = Lead::whereIn('unit_id', $adminUnits)
            ->with(['affiliatorProject.user', 'unit.project'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent withdrawals (now using project_id)
        $recent_withdrawals = CommissionWithdrawal::whereIn('project_id', $adminProjects)
            ->with(['user', 'project'])
            ->latest()
            ->limit(10)
            ->get();

        // Recent activities
        $recent_activities = ActivityLog::where(function($query) use ($adminProjects, $adminUnits) {
                $query->whereIn('project_id', $adminProjects)
                      ->orWhereIn('unit_id', $adminUnits);
            })
            ->with('user')
            ->latest()
            ->limit(15)
            ->get();

        // Monthly stats for charts
        $monthly_stats = $this->getMonthlyStats($adminUnits, $adminProjects);
        
        return view('pages.admin.dashboard.index', compact(
            'stats', 
            'projects', 
            'recent_leads', 
            'recent_withdrawals', 
            'recent_activities',
            'monthly_stats'
        ));
    }

    private function getMonthlyStats($unitIds, $projectIds)
    {
        $year = date('Y');
        $months = [];
        
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
            $monthKey = "$year-$month";
            
            // Leads per month (from units)
            $leads = Lead::whereIn('unit_id', $unitIds)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->count();
            
            $verifiedLeads = Lead::whereIn('unit_id', $unitIds)
                ->verified()
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->count();
            
            // Commission per month (from units)
            $commission = CommissionHistory::whereIn('unit_id', $unitIds)
                ->where('type', 'earned')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $i)
                ->sum('amount');
            
            $months[$monthKey] = [
                'leads' => $leads,
                'verified_leads' => $verifiedLeads,
                'commission' => $commission
            ];
        }
        
        return $months;
    }

    public function getStats()
    {
        $admin = User::find(Auth::id());
        $adminProjects = $admin->adminProjects()->pluck('projects.id');
        
        if ($adminProjects->isEmpty()) {
            return response()->json([]);
        }

        $adminUnits = Unit::whereIn('project_id', $adminProjects)->pluck('id');
        
        $stats = [
            'total_affiliators' => AffiliatorProject::whereIn('project_id', $adminProjects)
                ->distinct('user_id')->count('user_id'),
            'active_affiliators' => AffiliatorProject::whereIn('project_id', $adminProjects)
                ->where('status', 'active')->distinct('user_id')->count('user_id'),
            'total_leads' => Lead::whereIn('unit_id', $adminUnits)->count(),
            'verified_leads' => Lead::whereIn('unit_id', $adminUnits)->verified()->count(),
            'total_commission' => CommissionHistory::whereIn('unit_id', $adminUnits)
                ->where('type', 'earned')->sum('amount'),
            'pending_withdrawals' => CommissionWithdrawal::whereIn('project_id', $adminProjects)
                ->where('status', 'pending')->count(),
        ];
        
        return response()->json($stats);
    }
}