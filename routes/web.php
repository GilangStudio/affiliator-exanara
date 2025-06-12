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
use App\Http\Controllers\ProjectRegistrationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Affiliator\JoinProjectController;
use App\Http\Controllers\Admin\ProjectManagementController;
use App\Http\Controllers\Admin\WithdrawalManagementController;
use App\Http\Controllers\Affiliator\ProjectController as AffiliatorProjectController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\SuperAdmin\FaqController as SuperAdminFaqController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\Admin\AffiliatorController as AdminAffiliatorController;
use App\Http\Controllers\SuperAdmin\ProjectController as SuperAdminProjectController;
use App\Http\Controllers\SuperAdmin\SettingsController as SuperAdminSettingsController;
use App\Http\Controllers\Affiliator\DashboardController as AffiliatorDashboardController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\AffiliatorController as SuperAdminAffiliatorController;
use App\Http\Controllers\Admin\ProjectAffiliatorController as AdminProjectAffiliatorController;
use App\Http\Controllers\SuperAdmin\ProjectAdminController as SuperAdminProjectAdminController;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::get('/@{username}', [AuthController::class, 'show'])->name('profile.show');

Route::prefix('project')->name('affiliator.project-registration.')->group(function () {
    Route::get('/register', [ProjectRegistrationController::class, 'index'])->name('index');
    Route::post('/register', [ProjectRegistrationController::class, 'store'])->name('store');
    
    // AJAX Routes untuk mendapatkan data dropdown
    Route::prefix('ajax')->name('ajax.')->group(function () {
        Route::get('/unit-types', [ProjectRegistrationController::class, 'getUnitTypes'])->name('unit-types');
        Route::get('/commission-types', [ProjectRegistrationController::class, 'getCommissionTypes'])->name('commission-types');
    });
});

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

Route::middleware(['web', 'auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /*
    |--------------------------------------------------------------------------
    | Affiliator Routes (Role: affiliator)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:affiliator', 'active.user'])->group(function () {

        // Main join project page
        Route::get('/project/join', [JoinProjectController::class, 'index'])->name('affiliator.project.join.index');
        // Submit join project
        Route::post('/project/join', [JoinProjectController::class, 'joinProject'])->name('affiliator.project.join.store');

        // AJAX Routes untuk Join Project
        Route::prefix('ajax')->name('ajax.')->group(function () {
                
            // Get project details
            Route::get('/project/{project}/details', [JoinProjectController::class, 'getProjectDetails'])->name('project.details');
            
            // Get available projects
            Route::get('/projects/available', [JoinProjectController::class, 'getAvailableProjects'])->name('projects.available');
        });

        Route::middleware('check.project.affiliator')->group(function () {
            Route::name('affiliator.')->group(function () {
                Route::get('/dashboard', [AffiliatorDashboardController::class, 'index'])->name('dashboard');
                
                Route::prefix('project')->name('project.')->group(function () {
                    Route::get('/', [AffiliatorProjectController::class, 'index'])->name('index');
                    Route::get('/{project}', [AffiliatorProjectController::class, 'show'])->name('show');
                    Route::post('/{project}/toggle-status', [AffiliatorProjectController::class, 'toggleStatus'])->name('toggle-status');

                    // AJAX Routes
                    Route::prefix('ajax')->name('ajax.')->group(function () {
                        
                        Route::get('/{project}/statistics', [App\Http\Controllers\Affiliator\AffiliatorProjectController::class, 'statistics'])->name('statistics');
                        Route::get('/locations', [App\Http\Controllers\Affiliator\AffiliatorProjectController::class, 'getLocations'])->name('locations');
                    });
                });
        
                Route::prefix('leads')->name('leads.')->group(function () {
                    // List semua leads dari semua project
                    Route::get('/', [AffiliatorLeadsController::class, 'index'])->name('index');
                    
                    // Create lead form (pilih project dulu)
                    Route::get('/create', [App\Http\Controllers\Affiliator\LeadController::class, 'create'])->name('create');
                    
                    // Store lead
                    Route::post('/', [App\Http\Controllers\Affiliator\LeadController::class, 'store'])->name('store');
                    
                    // Leads per project (using slug)
                    Route::get('/project/{slug}', [App\Http\Controllers\Affiliator\LeadController::class, 'byProject'])->name('project');
                    
                    // Create lead untuk specific project
                    Route::get('/project/{slug}/create', [App\Http\Controllers\Affiliator\LeadController::class, 'createForProject'])->name('project.create');
                    
                    // View single lead
                    Route::get('/{lead}', [App\Http\Controllers\Affiliator\LeadController::class, 'show'])->name('show');
                    
                    // Edit lead (only if still pending)
                    Route::get('/{lead}/edit', [App\Http\Controllers\Affiliator\LeadController::class, 'edit'])->name('edit');
                    Route::put('/{lead}', [App\Http\Controllers\Affiliator\LeadController::class, 'update'])->name('update');
                    
                    // Delete lead (only if still pending)
                    Route::delete('/{lead}', [App\Http\Controllers\Affiliator\LeadController::class, 'destroy'])->name('destroy');
                    
                    // AJAX routes
                    Route::prefix('ajax')->name('ajax.')->group(function () {
                        // Get units by project
                        Route::get('/project/{project}/units', [App\Http\Controllers\Affiliator\LeadController::class, 'getProjectUnits'])->name('project.units');
                        
                        // Check duplicate lead
                        Route::post('/check-duplicate', [App\Http\Controllers\Affiliator\LeadController::class, 'checkDuplicate'])->name('check-duplicate');
                        
                        // Get lead statistics
                        Route::get('/statistics', [App\Http\Controllers\Affiliator\LeadController::class, 'statistics'])->name('statistics');
                    });
                });
            });             
        });
        
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (Role: admin)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:admin', 'active.user'])->group(function () {
        
        Route::prefix('admin')->name('admin.')->group(function () {
            
            // Admin Dashboard
            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
            
            // Project Management
            Route::prefix('projects')->name('projects.')->group(function () {
                Route::get('/', [AdminProjectController::class, 'index'])->name('index');
                Route::get('/{project}', [AdminProjectController::class, 'show'])->name('show');
                Route::get('/{project}/edit', [AdminProjectController::class, 'edit'])->name('edit');
                Route::put('/{project}', [AdminProjectController::class, 'update'])->name('update');
                Route::patch('/{project}/toggle-status', [AdminProjectController::class, 'toggleStatus'])->name('toggle-status');
                Route::get('/{project}/statistics', [AdminProjectController::class, 'statistics'])->name('statistics');

                Route::prefix('{project}')->group(function () {
                    // Affiliator Management per Project
                    Route::prefix('affiliators')->name('affiliators.')->group(function () {
                        Route::get('/', [AdminProjectAffiliatorController::class, 'index'])->name('index');
                        Route::get('/export', [AdminProjectAffiliatorController::class, 'export'])->name('export');
                        
                    });
                    
                    // Commission Withdrawal Management per Project
                    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
                        Route::get('/', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'index'])->name('index');
                        Route::get('/pending', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'pending'])->name('pending');
                        Route::get('/{withdrawal}', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'show'])->name('show');
                        Route::post('/{withdrawal}/approve', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'approve'])->name('approve');
                        Route::post('/{withdrawal}/reject', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'reject'])->name('reject');
                        Route::post('/{withdrawal}/process', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'process'])->name('process');
                        Route::post('/bulk-approve', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'bulkApprove'])->name('bulk-approve');
                        Route::post('/bulk-reject', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'bulkReject'])->name('bulk-reject');
                        Route::get('/export/csv', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'export'])->name('export');
                    });
                });
            });

            // Leads
            Route::get('/leads', [AdminLeadsController::class, 'index'])->name('leads.index');
            
            // Affiliator Management
            Route::prefix('affiliators')->name('affiliators.')->group(function () {
                Route::get('/', [AdminAffiliatorController::class, 'index'])->name('index');
                Route::get('/export', [AdminAffiliatorController::class, 'export'])->name('export');
                Route::get('/{affiliator}', [AdminAffiliatorController::class, 'show'])->name('show');
                Route::get('/{affiliator}/edit', [AdminAffiliatorController::class, 'edit'])->name('edit');
                Route::put('/{affiliator}', [AdminAffiliatorController::class, 'update'])->name('update');
                
                // AJAX Actions
                Route::post('/{affiliator}/verify-ktp', [AdminAffiliatorController::class, 'verifyKtp'])->name('verify-ktp');
                Route::post('/{affiliator}/reset-password', [AdminAffiliatorController::class, 'resetPassword'])->name('reset-password');
                Route::post('/{affiliator}/toggle-status', [AdminAffiliatorController::class, 'toggleStatus'])->name('toggle-status');
                
                // Statistics
                Route::get('/affiliators/statistics', [AdminAffiliatorController::class, 'statistics'])->name('statistics');
            });
            
            // Commission Withdrawal Management
            Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
                Route::get('/', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'index'])->name('index');
                Route::get('/pending', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'pending'])->name('pending');
                Route::get('/{withdrawal}', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'show'])->name('show');
                Route::post('/{withdrawal}/approve', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'approve'])->name('approve');
                Route::post('/{withdrawal}/reject', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'reject'])->name('reject');
                Route::post('/{withdrawal}/process', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'process'])->name('process');
                
                // Bulk actions
                Route::post('/bulk-approve', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'bulkApprove'])->name('bulk-approve');
                Route::post('/bulk-reject', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'bulkReject'])->name('bulk-reject');
                
                Route::get('/statistics/data', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'statistics'])->name('statistics');
                Route::get('/export/csv', [App\Http\Controllers\Admin\AdminWithdrawalController::class, 'export'])->name('export');
            });
            
            // Profile Management
            Route::prefix('profile')->name('profile.')->group(function () {
                Route::get('/', [AdminProfileController::class, 'index'])->name('index');
                Route::put('/update', [AdminProfileController::class, 'update'])->name('update');
                Route::put('/password', [AdminProfileController::class, 'changePassword'])->name('password.change');
                Route::delete('/photo', [AdminProfileController::class, 'deletePhoto'])->name('photo.delete');
            });
            
        });
        
    });


    /*
    |--------------------------------------------------------------------------
    | Super Admin Routes (Role: superadmin)
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:superadmin'])->group(function () {
        
        Route::prefix('s')->name('superadmin.')->group(function () {
            
            Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

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

                // Registration Management Routes (integrated)
                Route::post('/{project}/approve-registration', [SuperAdminProjectController::class, 'approveRegistration'])->name('approve-registration');
                Route::post('/{project}/reject-registration', [SuperAdminProjectController::class, 'rejectRegistration'])->name('reject-registration');
                Route::post('/bulk-approve-registrations', [SuperAdminProjectController::class, 'bulkApproveRegistrations'])->name('bulk-approve-registrations');
                // Route::get('/{project}/registration-detail', [SuperAdminProjectController::class, 'registrationDetail'])->name('registration-detail');
                
                Route::get('/api/crm-projects', [SuperAdminProjectController::class, 'getCrmProjects'])->name('crm-projects');
                Route::get('/api/crm-project-details/{id}', [SuperAdminProjectController::class, 'getCrmProjectDetails'])->name('crm-project-details');

                Route::middleware(['approved.project'])->group(function () {
                    // Project Admins Management
                    Route::get('/{project}/admins', [SuperAdminProjectAdminController::class, 'index'])->name('admins.index');
                    Route::get('/{project}/admins/create', [SuperAdminProjectAdminController::class, 'create'])->name('admins.create');
                    Route::post('/{project}/admins', [SuperAdminProjectAdminController::class, 'store'])->name('admins.store');
                    Route::get('/{project}/admins/{admin}/edit', [SuperAdminProjectAdminController::class, 'edit'])->name('admins.edit');
                    Route::put('/{project}/admins/{admin}', [SuperAdminProjectAdminController::class, 'update'])->name('admins.update');
                    Route::delete('/{project}/admins/{admin}', [SuperAdminProjectAdminController::class, 'destroy'])->name('admins.destroy');
                    Route::patch('/{project}/admins/{admin}/toggle-status', [SuperAdminProjectAdminController::class, 'toggleStatus'])->name('admins.toggle-status');
                    Route::post('/{project}/admins/{admin}/reset-password', [SuperAdminProjectAdminController::class, 'resetPassword'])->name('admins.reset-password');

                    // Project Units Management
                    Route::get('/{project}/units', [App\Http\Controllers\SuperAdmin\UnitController::class, 'index'])->name('units.index');
                    Route::get('/{project}/units/create', [App\Http\Controllers\SuperAdmin\UnitController::class, 'create'])->name('units.create');
                    Route::post('/{project}/units', [App\Http\Controllers\SuperAdmin\UnitController::class, 'store'])->name('units.store');
                    Route::get('/{project}/units/{unit}/edit', [App\Http\Controllers\SuperAdmin\UnitController::class, 'edit'])->name('units.edit');
                    Route::put('/{project}/units/{unit}', [App\Http\Controllers\SuperAdmin\UnitController::class, 'update'])->name('units.update');
                    Route::delete('/{project}/units/{unit}', [App\Http\Controllers\SuperAdmin\UnitController::class, 'destroy'])->name('units.destroy');
                    Route::patch('/{project}/units/{unit}/toggle-status', [App\Http\Controllers\SuperAdmin\UnitController::class, 'toggleStatus'])->name('units.toggle-status');
                });
            });

            Route::resource('faqs', SuperAdminFaqController::class);
            Route::post('faqs/reorder', [SuperAdminFaqController::class, 'reorder'])->name('faqs.reorder');
            Route::patch('faqs/{faq}/toggle-status', [SuperAdminFaqController::class, 'toggleStatus'])->name('faqs.toggle-status');

            
            // System Settings
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [SuperAdminSettingsController::class, 'index'])->name('index');
                
                // General Settings
                Route::put('/general', [SuperAdminSettingsController::class, 'updateGeneral'])->name('general.update');
                
                // Commission Settings
                Route::put('/commission', [SuperAdminSettingsController::class, 'updateCommission'])->name('commission.update');
                
                // Notification Settings
                Route::put('/notification', [SuperAdminSettingsController::class, 'updateNotification'])->name('notification.update');
                
                // Security Settings
                Route::put('/security', [SuperAdminSettingsController::class, 'updateSecurity'])->name('security.update');
                
                // Profile Settings
                Route::put('/profile', [SuperAdminSettingsController::class, 'updateProfile'])->name('profile.update');
                Route::put('/password', [SuperAdminSettingsController::class, 'changePassword'])->name('password.change');
                Route::delete('/photo', [SuperAdminSettingsController::class, 'deleteProfilePhoto'])->name('photo.delete');
                
                // Maintenance Settings
                Route::post('/maintenance/toggle', [SuperAdminSettingsController::class, 'toggleMaintenance'])->name('maintenance.toggle');
                Route::post('/cache/clear', [SuperAdminSettingsController::class, 'clearCache'])->name('cache.clear');
                Route::post('/backup/create', [SuperAdminSettingsController::class, 'createBackup'])->name('backup.create');
            });
            
        });

    });
    
});

/*
|--------------------------------------------------------------------------
| Error Pages
|--------------------------------------------------------------------------
*/

// Route::fallback(function () {
//     return response()->view('errors.404', [], 404);
// });