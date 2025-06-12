<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get project from route parameter
        $project = $request->route('project');
        
        if (!$project) {
            abort(404, 'Project tidak ditemukan');
        }

        // Check if project is approved
        // For manual registration projects, must be approved
        // For internal/CRM projects, automatically considered approved
        if ($project->is_manual_registration && $project->registration_status !== 'approved') {
            return redirect()->route('superadmin.projects.show', $project)
                ->with('error', 'Project ini belum disetujui. Silakan setujui project terlebih dahulu sebelum mengelola admin atau unit.');
        }

        return $next($request);
    }
}
