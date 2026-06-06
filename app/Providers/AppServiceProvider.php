<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\App;
use Illuminate\Pagination\Paginator;
use App\Services\CaseService;
use App\Services\PatientService;
use App\Services\TicketService;
use App\Services\ImageProcessingService;
use App\Services\ZipCompressionService;
use App\Repositories\CaseRepository;
use App\Repositories\PatientRepository;
use App\Repositories\UserRepository;
use App\Providers\GoogleDriveService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Services
        $this->app->singleton(CaseService::class, function ($app) {
            return new CaseService(
                $app->make(GoogleDriveService::class),
                $app->make(ImageProcessingService::class)
            );
        });

        $this->app->singleton(PatientService::class, function ($app) {
            return new PatientService($app->make(UserRepository::class));
        });

        $this->app->singleton(TicketService::class, function ($app) {
            return new TicketService($app->make(UserRepository::class));
        });

        $this->app->singleton(\App\Services\TechnicianService::class, function ($app) {
            return new \App\Services\TechnicianService(
                $app->make(CaseRepository::class),
                $app->make(UserRepository::class)
            );
        });

        // Bind Repositories
        $this->app->singleton(CaseRepository::class, function ($app) {
            return new CaseRepository($app->make(\App\Models\CasePatient::class));
        });

        $this->app->singleton(PatientRepository::class, function ($app) {
            return new PatientRepository($app->make(\App\Models\Patient::class));
        });

        $this->app->singleton(UserRepository::class, function ($app) {
            return new UserRepository($app->make(\App\Models\User::class));
        });

        // Bind GoogleDriveService
        $this->app->singleton(GoogleDriveService::class, function ($app) {
            return new GoogleDriveService();
        });

        // Bind ImageProcessingService
        $this->app->singleton(ImageProcessingService::class, function ($app) {
            return new ImageProcessingService();
        });

        // Bind ZipCompressionService
        $this->app->singleton(ZipCompressionService::class, function ($app) {
            return new ZipCompressionService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // The UI is Bootstrap-based; use Bootstrap 5 pagination markup instead
        // of the default Tailwind view (which rendered huge unstyled SVG arrows).
        Paginator::useBootstrapFive();
    }
}
