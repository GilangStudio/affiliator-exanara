<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceMiddleware
{
    /**
     * Routes yang tetap bisa diakses saat maintenance mode
     */
    protected $allowedRoutes = [
        'login',
        'login.process',
        'logout',
        'register',
        'register.process',
        'forgot-password',
        'forgot-password.process',
        'reset-password',
        'reset-password.process',
    ];

    /**
     * URL patterns yang tetap bisa diakses saat maintenance mode
     */
    protected $allowedPatterns = [
        'login*',
        'logout*',
        'register*',
        'forgot-password*',
        'reset-password*',
        'verify-email*',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Check if maintenance mode is enabled
            $maintenanceMode = SystemSetting::getValue('maintenance_mode', false);
            
            if (!$maintenanceMode) {
                return $next($request);
            }

            // Allow superadmin to access even in maintenance mode
            if (Auth::check() && Auth::user()->role === 'superadmin') {
                return $next($request);
            }

            // Allow access to specific routes
            $currentRoute = Route::currentRouteName();
            if (in_array($currentRoute, $this->allowedRoutes)) {
                return $next($request);
            }

            // Allow access to route patterns
            foreach ($this->allowedPatterns as $pattern) {
                if ($request->routeIs($pattern)) {
                    return $next($request);
                }
            }

            // Allow API endpoints untuk health check
            if ($request->is('api/health') || $request->is('up')) {
                return $next($request);
            }

            // Allow assets dan file statis
            if ($request->is('css/*') || $request->is('js/*') || $request->is('img/*') || 
                $request->is('fonts/*') || $request->is('storage/*') || $request->is('libs/*')) {
                return $next($request);
            }

            // Show maintenance page for other users
            $maintenanceMessage = SystemSetting::getValue(
                'maintenance_message', 
                'Sistem sedang dalam pemeliharaan. Silakan coba lagi nanti.'
            );

            // Handle AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Service Unavailable',
                    'maintenance_message' => $maintenanceMessage,
                    'retry_after' => 300, // 5 minutes
                ], 503);
            }

            // Show maintenance page
            return response()->view('errors.maintenance', [
                'message' => $maintenanceMessage,
                'retry_after' => 300,
            ], 503)->header('Retry-After', 300);

        } catch (\Exception $e) {
            // Jika terjadi error saat mengecek maintenance mode, lanjutkan request
            // untuk menghindari aplikasi crash
            Log::error('MaintenanceMiddleware error: ' . $e->getMessage());
            return $next($request);
        }
    }
}