<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\Stock;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
        
        // Automatically update reason column based on expiry status
        $this->updateExpiryStatus();
        
        // Schedule daily update of expiry status
        if ($this->app->runningInConsole()) {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $schedule->call(function () {
                $this->updateExpiryStatus();
            })->daily();
        }
    }
    
    /**
     * Update expiry status for all products
     */
    private function updateExpiryStatus(): void
    {
        // Update expired products (today or earlier)
        \App\Models\Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '<=', now()->startOfDay())
            ->update(['reason' => 'expired']);

        // Update near-expiry products (within 6 months)
        \App\Models\Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now()->startOfDay())
            ->whereDate('expiryDate', '<=', now()->addMonths(6)->startOfDay())
            ->update(['reason' => 'near_expiry']);

        // Update safe products (beyond 6 months)
        \App\Models\Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now()->addMonths(6)->startOfDay())
            ->update(['reason' => 'safe']);
    }
}
