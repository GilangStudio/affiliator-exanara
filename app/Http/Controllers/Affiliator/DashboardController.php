<?php

namespace App\Http\Controllers\Affiliator;

use App\Http\Controllers\Controller;

class DashboardController extends Controller 
{
    public function index()
    {
        // dd('Affiliator Dashboard');
        return view('pages.affiliator.dashboard.index');
    }
}