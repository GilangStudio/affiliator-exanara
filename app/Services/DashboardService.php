<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\User;
use App\Models\Project;
use App\Models\ActivityLog;
use App\Models\SupportTicket;
use App\Models\AffiliatorProject;
use App\Models\CommissionHistory;
use Illuminate\Support\Facades\DB;
use App\Models\CommissionWithdrawal;

class DashboardService
{
    /**
     * Get Super Admin dashboard statistics
     */
    public function getSuperAdminStats()
    {
        return [
            'total_users' => User::where('role', '!=', 'superadmin')->count(),
            'total_admins' => User::admins()->count(),
            'active_admins' => User::admins()->active()->count(),
            'total_affiliators' => User::affiliators()->count(),
            'active_affiliators' => User::affiliators()->active()->count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::active()->count(),
            'total_leads' => Lead::count(),
            'verified_leads' => Lead::verified()->count(),
            'pending_leads' => Lead::where('verification_status', 'pending')->count(),
            'total_commission_earned' => CommissionHistory::earned()->sum('amount'),
            'total_commission_withdrawn' => CommissionHistory::withdrawn()->sum('amount'),
            'pending_withdrawals' => CommissionWithdrawal::pending()->count(),
            'support_tickets_open' => SupportTicket::whereIn('status', ['open', 'in_progress'])->count(),
        ];
    }

    /**
     * Get Admin dashboard statistics
     */
    public function getAdminStats($userId)
    {
        $user = User::find($userId);
        $projectIds = $user->adminProjects()->pluck('projects.id');

        return [
            'total_projects' => $projectIds->count(),
            'total_affiliators' => AffiliatorProject::whereIn('project_id', $projectIds)
                ->distinct('user_id')->count('user_id'),
            'active_affiliators' => AffiliatorProject::whereIn('project_id', $projectIds)
                ->where('status', 'active')->distinct('user_id')->count('user_id'),
            'total_leads' => Lead::whereHas('affiliatorProject', function($query) use ($projectIds) {
                $query->whereIn('project_id', $projectIds);
            })->count(),
            'verified_leads' => Lead::verified()->whereHas('affiliatorProject', function($query) use ($projectIds) {
                $query->whereIn('project_id', $projectIds);
            })->count(),
            'pending_leads' => Lead::where('verification_status', 'pending')
                ->whereHas('affiliatorProject', function($query) use ($projectIds) {
                    $query->whereIn('project_id', $projectIds);
                })->count(),
            'total_commission_paid' => CommissionHistory::earned()
                ->whereIn('project_id', $projectIds)->sum('amount'),
            'pending_withdrawals' => \App\Models\CommissionWithdrawal::pending()
                ->whereIn('project_id', $projectIds)->count(),
        ];
    }

    /**
     * Get Affiliator dashboard statistics
     */
    public function getAffiliatorStats($userId)
    {
        $user = User::find($userId);
        
        return [
            'total_projects' => $user->affiliatorProjects()->count(),
            'active_projects' => $user->affiliatorProjects()->where('status', 'active')->count(),
            'pending_verification' => $user->affiliatorProjects()->where('verification_status', 'pending')->count(),
            'total_leads' => Lead::whereHas('affiliatorProject', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count(),
            'verified_leads' => Lead::verified()->whereHas('affiliatorProject', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count(),
            'pending_leads' => Lead::where('verification_status', 'pending')
                ->whereHas('affiliatorProject', function($query) use ($userId) {
                    $query->where('user_id', $userId);
                })->count(),
            'total_commission_earned' => CommissionHistory::earned()->where('user_id', $userId)->sum('amount'),
            'total_commission_withdrawn' => CommissionHistory::withdrawn()->where('user_id', $userId)->sum('amount'),
            'available_commission' => $this->getAvailableCommission($userId),
            'pending_withdrawals' => \App\Models\CommissionWithdrawal::pending()->where('user_id', $userId)->count(),
        ];
    }

    /**
     * Get monthly statistics for charts
     */
    public function getMonthlyStats($year = null, $projectIds = null, $userId = null)
    {
        $year = $year ?: date('Y');
        
        $leadsQuery = Lead::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', $year);

        $commissionsQuery = CommissionHistory::earned()
            ->selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->whereYear('created_at', $year);

        if ($projectIds) {
            $leadsQuery->whereHas('affiliatorProject', function($query) use ($projectIds) {
                $query->whereIn('project_id', $projectIds);
            });
            $commissionsQuery->whereIn('project_id', $projectIds);
        }

        if ($userId) {
            $leadsQuery->whereHas('affiliatorProject', function($query) use ($userId) {
                $query->where('user_id', $userId);
            });
            $commissionsQuery->where('user_id', $userId);
        }

        return [
            'leads' => $leadsQuery->groupBy('month')->pluck('count', 'month')->toArray(),
            'commissions' => $commissionsQuery->groupBy('month')->pluck('total', 'month')->toArray(),
        ];
    }

    /**
     * Get recent activities
     */
    public function getRecentActivities($limit = 10, $projectIds = null, $userId = null)
    {
        $query = ActivityLog::with(['user:id,name,email,role', 'project:id,name'])
            ->latest();

        if ($projectIds) {
            $query->whereIn('project_id', $projectIds);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get top performing projects
     */
    public function getTopProjects($limit = 5, $projectIds = null)
    {
        $query = Project::withCount(['leads as verified_leads_count' => function($query) {
                $query->verified();
            }])
            ->with(['leads' => function($query) {
                $query->verified();
            }])
            ->withSum(['commissionHistories as total_commission' => function($query) {
                $query->where('type', 'earned');
            }], 'amount');

        if ($projectIds) {
            $query->whereIn('id', $projectIds);
        }

        return $query->orderByDesc('verified_leads_count')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top performing affiliators
     */
    public function getTopAffiliators($limit = 5, $projectIds = null)
    {
        $query = User::affiliators()
            ->withCount(['affiliatorProjects as total_leads' => function($query) use ($projectIds) {
                $query->join('leads', 'affiliator_projects.id', '=', 'leads.affiliator_project_id')
                    ->verified();
                if ($projectIds) {
                    $query->whereIn('affiliator_projects.project_id', $projectIds);
                }
            }])
            ->withSum(['commissionHistories as total_commission' => function($query) use ($projectIds) {
                $query->where('type', 'earned');
                if ($projectIds) {
                    $query->whereIn('project_id', $projectIds);
                }
            }], 'amount');

        return $query->orderByDesc('total_commission')
            ->limit($limit)
            ->get();
    }

    /**
     * Get commission leaderboard
     */
    public function getCommissionLeaderboard($period = 'month', $limit = 10, $projectIds = null)
    {
        $query = CommissionHistory::with('user:id,name,email')
            ->where('type', 'earned');

        if ($projectIds) {
            $query->whereIn('project_id', $projectIds);
        }

        // Apply period filter
        switch ($period) {
            case 'week':
                $query->where('created_at', '>=', now()->startOfWeek());
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }

        return $query->selectRaw('user_id, SUM(amount) as total_commission, COUNT(*) as total_leads')
            ->groupBy('user_id')
            ->orderBy('total_commission', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get latest withdrawals for admin
     */
    public function getLatestWithdrawals($limit = 5, $projectIds = null)
    {
        $query = \App\Models\CommissionWithdrawal::with(['user:id,name,email', 'project:id,name', 'bankAccount'])
            ->latest();

        if ($projectIds) {
            $query->whereIn('project_id', $projectIds);
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get user's recent leads
     */
    public function getUserRecentLeads($userId, $limit = 5)
    {
        return Lead::with(['affiliatorProject.project:id,name'])
            ->whereHas('affiliatorProject', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get user's recent commission history
     */
    public function getUserRecentCommissions($userId, $limit = 5)
    {
        return CommissionHistory::with(['project:id,name', 'lead:id,customer_name'])
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate available commission for user
     */
    private function getAvailableCommission($userId)
    {
        $earned = CommissionHistory::earned()->where('user_id', $userId)->sum('amount');
        $withdrawn = CommissionHistory::withdrawn()->where('user_id', $userId)->sum('amount');
        $adjustments = CommissionHistory::where('type', 'adjustment')->where('user_id', $userId)->sum('amount');
        
        return $earned - $withdrawn + $adjustments;
    }

    /**
     * Get dashboard data based on user role
     */
    public function getDashboardData($user)
    {
        switch ($user->role) {
            case 'superadmin':
                return [
                    'stats' => $this->getSuperAdminStats(),
                    'monthly_stats' => $this->getMonthlyStats(),
                    'recent_activities' => $this->getRecentActivities(),
                    'top_projects' => $this->getTopProjects(),
                    'top_affiliators' => $this->getTopAffiliators(),
                    'commission_leaderboard' => $this->getCommissionLeaderboard(),
                    'latest_withdrawals' => $this->getLatestWithdrawals(),
                ];

            case 'admin':
                $projectIds = $user->adminProjects()->pluck('projects.id');
                return [
                    'stats' => $this->getAdminStats($user->id),
                    'monthly_stats' => $this->getMonthlyStats(null, $projectIds),
                    'recent_activities' => $this->getRecentActivities(10, $projectIds),
                    'top_projects' => $this->getTopProjects(5, $projectIds),
                    'top_affiliators' => $this->getTopAffiliators(5, $projectIds),
                    'commission_leaderboard' => $this->getCommissionLeaderboard('month', 10, $projectIds),
                    'latest_withdrawals' => $this->getLatestWithdrawals(5, $projectIds),
                ];

            case 'affiliator':
                return [
                    'stats' => $this->getAffiliatorStats($user->id),
                    'monthly_stats' => $this->getMonthlyStats(null, null, $user->id),
                    'recent_activities' => $this->getRecentActivities(10, null, $user->id),
                    'recent_leads' => $this->getUserRecentLeads($user->id),
                    'recent_commissions' => $this->getUserRecentCommissions($user->id),
                    'projects' => $user->affiliatorProjects()->with('project')->get(),
                ];

            default:
                return [];
        }
    }
}