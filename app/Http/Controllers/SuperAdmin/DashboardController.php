<?php

namespace App\Http\Controllers\SuperAdmin;
use App\Services\DashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    protected $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }
    
    public function index()
    {
        $user = Auth::user();
        $data = $this->dashboardService->getDashboardData($user);

        return view('pages.superadmin.dashboard.index', compact('data'));
    }
}