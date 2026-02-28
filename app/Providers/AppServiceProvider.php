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

        // Only run if NOT running migrations
        if (!$this->app->runningInConsole()) {
            $this->updateExpiryStatus();
        }
    }
    
    /**
     * Update expiry status for all products
     */
    private function updateExpiryStatus(): void
    {
        // Update expired products (today or earlier)
        Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '<=', now()->startOfDay())
            ->update(['reason' => 'expired']);

        // Update near-expiry products (within 6 months)
        Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now()->startOfDay())
            ->whereDate('expiryDate', '<=', now()->addMonths(6)->startOfDay())
            ->update(['reason' => 'near_expiry']);

        // Update safe products (beyond 6 months)
        Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now()->addMonths(6)->startOfDay())
            ->update(['reason' => 'safe']);
    }
}
