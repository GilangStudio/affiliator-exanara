<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons for better performance
        $this->app->singleton(\App\Services\UserService::class);
        $this->app->singleton(\App\Services\LeadService::class);
        $this->app->singleton(\App\Services\CommissionService::class);
        $this->app->singleton(\App\Services\NotificationService::class);
        $this->app->singleton(\App\Services\ActivityLogService::class);
        $this->app->singleton(\App\Services\CrmService::class);
        $this->app->singleton(\App\Services\BankAccountService::class);
        $this->app->singleton(\App\Services\AffiliatorProjectService::class);
        $this->app->singleton(\App\Services\SupportTicketService::class);
        // $this->app->singleton(\App\Services\WhatsAppService::class);
        // $this->app->singleton(\App\Services\EmailService::class);

        // Bind interfaces to implementations if needed
        // $this->app->bind(SomeInterface::class, SomeImplementation::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
