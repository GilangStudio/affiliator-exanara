<?php

// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\BankAccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SupportTicketController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ProjectManagementController;
use App\Http\Controllers\Admin\WithdrawalManagementController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\ProjectController as SuperAdminProjectController;
use App\Http\Controllers\SuperAdmin\ProjectAdminController as SuperAdminProjectAdminController;
use App\Http\Controllers\SuperAdmin\AffiliatorController as SuperAdminAffiliatorController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Landing page
// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

// Authentication routes
Route::middleware(['guest'])->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginProcess'])->name('login.process');
    
    // Register
    Route::get('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/register', [AuthController::class, 'registerProcess'])->name('register.process');
    
    // Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password');
    Route::post('/forgot-password', [AuthController::class, 'forgotPasswordProcess'])->name('forgot-password.process');
    
    // Reset Password
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('reset-password');
    Route::post('/reset-password', [AuthController::class, 'resetPasswordProcess'])->name('reset-password.process');
    
    // Email Verification
    Route::get('/verify-email/{token}', [AuthController::class, 'verifyAccount'])->name('verify-email');
    Route::post('/resend-verification', [AuthController::class, 'resendVerification'])->name('resend-verification');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (All logged-in users)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // // Dashboard (role-based redirect handled in controller)
    // Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // // Profile Management
    // Route::prefix('profile')->name('profile.')->group(function () {
    //     Route::get('/', [UserController::class, 'profile'])->name('show');
    //     Route::put('/', [UserController::class, 'updateProfile'])->name('update');
    //     Route::post('/photo', [UserController::class, 'updatePhoto'])->name('photo.update');
    //     Route::delete('/photo', [UserController::class, 'deletePhoto'])->name('photo.delete');
    //     Route::put('/password', [UserController::class, 'changePassword'])->name('password.change');
    // });
    
    // // Notifications
    // Route::prefix('notifications')->name('notifications.')->group(function () {
    //     Route::get('/', [NotificationController::class, 'index'])->name('index');
    //     Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    //     Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    //     Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    // });
    
    // // Support System
    // Route::prefix('support')->name('support.')->group(function () {
    //     Route::get('/', [SupportTicketController::class, 'index'])->name('index');
    //     Route::get('/create', [SupportTicketController::class, 'create'])->name('create');
    //     Route::post('/', [SupportTicketController::class, 'store'])->name('store');
    //     Route::get('/{ticket}', [SupportTicketController::class, 'show'])->name('show');
    //     Route::post('/{ticket}/close', [SupportTicketController::class, 'close'])->name('close');
        
    //     // FAQ
    //     Route::get('/faq', [SupportTicketController::class, 'faq'])->name('faq');
    // });

    /*
    |--------------------------------------------------------------------------
    | Affiliator Routes (Role: affiliator)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:affiliator'])->group(function () {
        
        Route::prefix('affiliator')->name('affiliator.')->group(function () {
            
            // Affiliator Dashboard
            Route::get('/dashboard', [DashboardController::class, 'affiliator'])->name('dashboard');
            
            // Project Management
            Route::prefix('projects')->name('projects.')->group(function () {
                Route::get('/', [ProjectController::class, 'affiliatorProjects'])->name('index');
                Route::get('/{project}', [ProjectController::class, 'affiliatorProjectDetail'])->name('show');
                Route::post('/{project}/join', [ProjectController::class, 'joinProject'])->name('join');
                
                // KTP Verification
                Route::get('/{affiliatorProject}/ktp', [ProjectController::class, 'ktpForm'])->name('ktp.form');
                Route::post('/{affiliatorProject}/ktp', [ProjectController::class, 'uploadKtp'])->name('ktp.upload');
                
                // Terms & Conditions
                Route::get('/{affiliatorProject}/terms', [ProjectController::class, 'termsForm'])->name('terms.form');
                Route::post('/{affiliatorProject}/terms', [ProjectController::class, 'acceptTerms'])->name('terms.accept');
                
                // Digital Signature
                Route::get('/{affiliatorProject}/signature', [ProjectController::class, 'signatureForm'])->name('signature.form');
                Route::post('/{affiliatorProject}/signature', [ProjectController::class, 'saveSignature'])->name('signature.save');
            });
            
            // Lead Management
            Route::prefix('leads')->name('leads.')->group(function () {
                Route::get('/', [LeadController::class, 'index'])->name('index');
                Route::get('/create', [LeadController::class, 'create'])->name('create');
                Route::post('/', [LeadController::class, 'store'])->name('store');
                Route::get('/{lead}', [LeadController::class, 'show'])->name('show');
                Route::get('/{lead}/edit', [LeadController::class, 'edit'])->name('edit');
                Route::put('/{lead}', [LeadController::class, 'update'])->name('update');
                
                // Lead Status & History
                Route::get('/{lead}/history', [LeadController::class, 'history'])->name('history');
                Route::get('/statistics', [LeadController::class, 'statistics'])->name('statistics');
            });
            
            // Commission Management
            Route::prefix('commission')->name('commission.')->group(function () {
                Route::get('/', [CommissionController::class, 'index'])->name('index');
                Route::get('/history', [CommissionController::class, 'history'])->name('history');
                Route::get('/withdraw', [CommissionController::class, 'withdrawForm'])->name('withdraw.form');
                Route::post('/withdraw', [CommissionController::class, 'withdraw'])->name('withdraw');
                Route::get('/withdrawals', [CommissionController::class, 'withdrawals'])->name('withdrawals');
                Route::delete('/withdrawals/{withdrawal}', [CommissionController::class, 'cancelWithdrawal'])->name('withdrawals.cancel');
                
                // Reports
                Route::get('/report', [CommissionController::class, 'report'])->name('report');
                Route::get('/leaderboard', [CommissionController::class, 'leaderboard'])->name('leaderboard');
            });
            
            // Bank Account Management
            Route::prefix('bank-accounts')->name('bank-accounts.')->group(function () {
                Route::get('/', [BankAccountController::class, 'index'])->name('index');
                Route::get('/create', [BankAccountController::class, 'create'])->name('create');
                Route::post('/', [BankAccountController::class, 'store'])->name('store');
                Route::get('/{bankAccount}/edit', [BankAccountController::class, 'edit'])->name('edit');
                Route::put('/{bankAccount}', [BankAccountController::class, 'update'])->name('update');
                Route::delete('/{bankAccount}', [BankAccountController::class, 'destroy'])->name('destroy');
            });
            
        });
        
    });

    /*
    |--------------------------------------------------------------------------
    | Super Admin Routes (Role: superadmin)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:superadmin'])->group(function () {
        
        // Admin Dashboard
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        Route::name('superadmin.')->group(function () {

            // User Management
            Route::resource('users', SuperAdminUserController::class);
            Route::prefix('users')->name('users.')->group(function () {
                Route::patch('/{user}/toggle-status', [SuperAdminUserController::class, 'toggleStatus'])->name('toggle-status');
                Route::post('/{user}/reset-password', [SuperAdminUserController::class,'resetPassword'])->name('reset-password');
            });

            // Affiliators Management
            Route::resource('affiliators', SuperAdminAffiliatorController::class);
            Route::prefix('affiliators')->name('affiliators.')->group(function () {
                Route::patch('/{affiliator}/toggle-status', [SuperAdminAffiliatorController::class, 'toggleStatus'])->name('toggle-status');
                Route::post('/{affiliator}/reset-password', [SuperAdminAffiliatorController::class, 'resetPassword'])->name('reset-password');
                Route::delete('/{affiliator}/remove-photo', [SuperAdminAffiliatorController::class, 'removePhoto'])->name('remove-photo');
            });

            // Projects Management
            Route::resource('projects', SuperAdminProjectController::class);
            Route::prefix('projects')->name('projects.')->group(function () {
                Route::patch('/{project}/toggle-status', [SuperAdminProjectController::class, 'toggleStatus'])->name('toggle-status');
                
                // Project Admins Management
                Route::get('/{project}/admins', [SuperAdminProjectAdminController::class, 'index'])->name('admins.index');
                Route::get('/{project}/admins/create', [SuperAdminProjectAdminController::class, 'create'])->name('admins.create');
                Route::post('/{project}/admins', [SuperAdminProjectAdminController::class, 'store'])->name('admins.store');
                Route::get('/{project}/admins/{admin}/edit', [SuperAdminProjectAdminController::class, 'edit'])->name('admins.edit');
                Route::put('/{project}/admins/{admin}', [SuperAdminProjectAdminController::class, 'update'])->name('admins.update');
                Route::delete('/{project}/admins/{admin}', [SuperAdminProjectAdminController::class, 'destroy'])->name('admins.destroy');
                Route::patch('/{project}/admins/{admin}/toggle-status', [SuperAdminProjectAdminController::class, 'toggleStatus'])->name('admins.toggle-status');
                Route::post('/{project}/admins/{admin}/reset-password', [SuperAdminProjectAdminController::class, 'resetPassword'])->name('admins.reset-password');
            });

            
            // System Settings
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [SystemSettingsController::class, 'index'])->name('index');
                Route::put('/', [SystemSettingsController::class, 'update'])->name('update');
                Route::post('/cache-clear', [SystemSettingsController::class, 'clearCache'])->name('cache-clear');
            });
            
            // System Maintenance
            Route::prefix('maintenance')->name('maintenance.')->group(function () {
                Route::get('/', [MaintenanceController::class, 'index'])->name('index');
                Route::post('/cleanup', [MaintenanceController::class, 'cleanup'])->name('cleanup');
                Route::get('/logs', [MaintenanceController::class, 'logs'])->name('logs');
                Route::get('/system-info', [MaintenanceController::class, 'systemInfo'])->name('system-info');
            });
            
            // Activity Monitoring
            Route::prefix('activity')->name('activity.')->group(function () {
                Route::get('/', [ActivityController::class, 'index'])->name('index');
                Route::get('/suspicious', [ActivityController::class, 'suspicious'])->name('suspicious');
                Route::get('/export', [ActivityController::class, 'export'])->name('export');
            });
            
        });
        
    });
    
});

/*
|--------------------------------------------------------------------------
| Admin Routes (Role: admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['admin'])->group(function () {
    
    Route::prefix('admin')->name('admin.')->group(function () {
        
        // Admin Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Project Management
        Route::resource('projects', ProjectManagementController::class);
        Route::prefix('projects/{project}')->name('projects.')->group(function () {
            Route::post('/activate', [ProjectManagementController::class, 'activate'])->name('activate');
            Route::post('/deactivate', [ProjectManagementController::class, 'deactivate'])->name('deactivate');
            
            // Project Admins
            Route::get('/admins', [ProjectManagementController::class, 'admins'])->name('admins');
            Route::post('/admins', [ProjectManagementController::class, 'addAdmin'])->name('admins.add');
            Route::delete('/admins/{user}', [ProjectManagementController::class, 'removeAdmin'])->name('admins.remove');
            
            // Project Statistics
            Route::get('/statistics', [ProjectManagementController::class, 'statistics'])->name('statistics');
            Route::get('/affiliators', [ProjectManagementController::class, 'affiliators'])->name('affiliators');
            Route::get('/leads', [ProjectManagementController::class, 'leads'])->name('leads');
        });
        
        // User Management
        Route::resource('users', UserManagementController::class);
        Route::prefix('users/{user}')->name('users.')->group(function () {
            Route::post('/activate', [UserManagementController::class, 'activate'])->name('activate');
            Route::post('/deactivate', [UserManagementController::class, 'deactivate'])->name('deactivate');
            Route::post('/change-role', [UserManagementController::class, 'changeRole'])->name('change-role');
            Route::get('/activity', [UserManagementController::class, 'activity'])->name('activity');
        });
        
        // Commission Withdrawal Management
        Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
            Route::get('/', [WithdrawalManagementController::class, 'index'])->name('index');
            Route::get('/pending', [WithdrawalManagementController::class, 'pending'])->name('pending');
            Route::get('/{withdrawal}', [WithdrawalManagementController::class, 'show'])->name('show');
            Route::post('/{withdrawal}/approve', [WithdrawalManagementController::class, 'approve'])->name('approve');
            Route::post('/{withdrawal}/reject', [WithdrawalManagementController::class, 'reject'])->name('reject');
            Route::post('/{withdrawal}/process', [WithdrawalManagementController::class, 'process'])->name('process');
            
            // Bulk actions
            Route::post('/bulk-approve', [WithdrawalManagementController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [WithdrawalManagementController::class, 'bulkReject'])->name('bulk-reject');
        });
        
        // Bank Account Verification
        Route::prefix('bank-accounts')->name('bank-accounts.')->group(function () {
            Route::get('/', [BankAccountController::class, 'adminIndex'])->name('index');
            Route::get('/pending', [BankAccountController::class, 'pendingVerification'])->name('pending');
            Route::post('/{bankAccount}/verify', [BankAccountController::class, 'verify'])->name('verify');
            Route::post('/{bankAccount}/reject', [BankAccountController::class, 'reject'])->name('reject');
        });
        
        // Support Ticket Management
        Route::prefix('support')->name('support.')->group(function () {
            Route::get('/', [SupportTicketController::class, 'adminIndex'])->name('index');
            Route::get('/open', [SupportTicketController::class, 'openTickets'])->name('open');
            Route::get('/{ticket}', [SupportTicketController::class, 'adminShow'])->name('show');
            Route::post('/{ticket}/assign', [SupportTicketController::class, 'assign'])->name('assign');
            Route::post('/{ticket}/resolve', [SupportTicketController::class, 'resolve'])->name('resolve');
            Route::post('/{ticket}/close', [SupportTicketController::class, 'adminClose'])->name('close');
            
            // FAQ Management
            Route::resource('faq', FaqController::class);
        });
        
        // Reports & Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'reports'])->name('index');
            Route::get('/commission', [AdminDashboardController::class, 'commissionReport'])->name('commission');
            Route::get('/leads', [AdminDashboardController::class, 'leadsReport'])->name('leads');
            Route::get('/affiliators', [AdminDashboardController::class, 'affiliatorsReport'])->name('affiliators');
            Route::get('/activity', [AdminDashboardController::class, 'activityReport'])->name('activity');
            
            // Export
            Route::post('/export/{type}', [AdminDashboardController::class, 'exportReport'])->name('export');
        });
        
    });
    
});

/*
|--------------------------------------------------------------------------
| AJAX/API Routes (for frontend interactions)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('ajax')->name('ajax.')->group(function () {
    
    // General AJAX endpoints
    Route::get('/notifications/unread', [NotificationController::class, 'unreadCount'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    
    // Lead-related AJAX
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/search', [LeadController::class, 'search'])->name('search');
        Route::get('/{lead}/status-history', [LeadController::class, 'statusHistory'])->name('status-history');
        Route::post('/{lead}/send-to-crm', [LeadController::class, 'sendToCrm'])->name('send-to-crm');
    });
    
    // Commission-related AJAX
    Route::prefix('commission')->name('commission.')->group(function () {
        Route::get('/stats', [CommissionController::class, 'ajaxStats'])->name('stats');
        Route::get('/validate-withdrawal', [CommissionController::class, 'validateWithdrawal'])->name('validate-withdrawal');
    });
    
    // Project-related AJAX
    Route::prefix('projects')->name('projects.')->group(function () {
        Route::get('/{project}/can-join', [ProjectController::class, 'canJoin'])->name('can-join');
        Route::get('/available', [ProjectController::class, 'availableProjects'])->name('available');
    });
    
});

/*
|--------------------------------------------------------------------------
| File Upload Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('upload')->name('upload.')->group(function () {
    Route::post('/profile-photo', [UserController::class, 'uploadProfilePhoto'])->name('profile-photo');
    Route::post('/ktp-photo', [ProjectController::class, 'uploadKtpPhoto'])->name('ktp-photo');
    Route::post('/project-logo', [ProjectManagementController::class, 'uploadLogo'])->name('project-logo');
    Route::post('/signature', [ProjectController::class, 'uploadSignature'])->name('signature');
});

/*
|--------------------------------------------------------------------------
| Error Pages
|--------------------------------------------------------------------------
*/

// Route::fallback(function () {
//     return response()->view('errors.404', [], 404);
// });