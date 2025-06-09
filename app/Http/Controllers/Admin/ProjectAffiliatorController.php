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

class ProjectAffiliatorController extends Controller
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
     * Display all affiliators for specific project.
     */
    public function index(Request $request, Project $project)
    {
        // Check if current admin manages this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $project->id)->exists()) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengelola affiliator project ini.');
        }

        $query = AffiliatorProject::where('project_id', $project->id)
            ->with(['user', 'verifiedBy']);

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
                });
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'created_at');
        $sortOrder = $request->get('order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $affiliators = $query->paginate(15);

        // Statistics for this project only
        $stats = [
            'total' => AffiliatorProject::where('project_id', $project->id)->count(),
            'pending' => AffiliatorProject::where('project_id', $project->id)->where('verification_status', 'pending')->count(),
            'verified' => AffiliatorProject::where('project_id', $project->id)->where('verification_status', 'verified')->count(),
            'active' => AffiliatorProject::where('project_id', $project->id)->where('status', 'active')->count(),
            'suspended' => AffiliatorProject::where('project_id', $project->id)->where('status', 'suspended')->count(),
        ];

        return view('pages.admin.projects.affiliators.index', compact('project', 'affiliators', 'stats'));
    }

    /**
     * Export affiliators to CSV for specific project
     */
    public function export(Request $request, Project $project)
    {
        // Check if current admin manages this project
        if (!User::find(Auth::user()->id)->adminProjects()->where('projects.id', $project->id)->exists()) {
            return redirect()->route('admin.projects.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengekspor data project ini.');
        }

        $query = AffiliatorProject::where('project_id', $project->id)
            ->with(['user', 'verifiedBy']);

        // Apply same filters as index
        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%")
                             ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        $affiliators = $query->get();

        $filename = 'affiliators_' . $project->slug . '_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($affiliators) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel UTF-8 support
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Header
            fputcsv($file, [
                'Nama',
                'Email', 
                'Telepon',
                'Project',
                'Status Verifikasi',
                'Status Affiliator',
                'Status User',
                'No. KTP',
                'Total Lead',
                'Lead Verified',
                'Total Komisi',
                'Bergabung',
                'Diverifikasi',
                'Diverifikasi Oleh',
                'Catatan Verifikasi'
            ]);

            // Data
            foreach ($affiliators as $affiliator) {
                $totalLeads = $affiliator->leads()->count();
                $verifiedLeads = $affiliator->leads()->verified()->count();
                $totalCommission = $affiliator->leads()->verified()->sum('commission_earned');

                fputcsv($file, [
                    $affiliator->user->name,
                    $affiliator->user->email,
                    $affiliator->user->phone_number,
                    $affiliator->project->name,
                    ucfirst($affiliator->verification_status),
                    $affiliator->status_label,
                    $affiliator->user->is_active ? 'Aktif' : 'Nonaktif',
                    $affiliator->ktp_number ?: '-',
                    $totalLeads,
                    $verifiedLeads,
                    'Rp ' . number_format($totalCommission, 0, ',', '.'),
                    $affiliator->created_at->format('d/m/Y H:i'),
                    $affiliator->verified_at ? $affiliator->verified_at->format('d/m/Y H:i') : '-',
                    $affiliator->verifiedBy ? $affiliator->verifiedBy->name : '-',
                    $affiliator->verification_notes ?: '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}