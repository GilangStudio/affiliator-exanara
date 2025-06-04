<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckProjectAffiliatorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = User::affiliators()->find(Auth::id());

        // Check if user is active
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')
                ->with('error', 'Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        // Check if user has any projects
        $hasProjects = $user->affiliatorProjects()->exists();
        if (!$hasProjects) {
            // Redirect to setup if no projects found
            return redirect()->route('affiliator.project.join.index')
                ->with('info', 'Silahkan bergabung dengan project terlebih dahulu untuk menggunakan sistem.');
        }

        // Check if user has at least one active project
        $hasActiveProject = $user->affiliatorProjects()
            ->where('status', 'active')
            ->exists();

        if (!$hasActiveProject) {
            // If no incomplete projects, redirect to project selection
            return redirect()->route('affiliator.setup.projects')
                ->with('info', 'Silahkan bergabung dengan project untuk melanjutkan.');
        }

        return $next($request);
    }
}
