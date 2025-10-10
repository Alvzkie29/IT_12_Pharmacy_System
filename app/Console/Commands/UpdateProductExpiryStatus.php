<?php

namespace App\Console\Commands;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateProductExpiryStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:update-expiry-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the reason column for expired and near-expiry products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating expiry status for products...');

        // Update expired products (today or earlier)
        $expiredCount = Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '<=', now()->startOfDay())
            ->whereNull('reason')
            ->update(['reason' => 'expired']);

        $this->info("Updated {$expiredCount} expired products.");

        // Update near-expiry products (within 6 months)
        $nearExpiryCount = Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now()->startOfDay())
            ->whereDate('expiryDate', '<=', now()->addMonths(6)->startOfDay())
            ->whereNull('reason')
            ->update(['reason' => 'near_expiry']);

        $this->info("Updated {$nearExpiryCount} near-expiry products.");

        // Update safe products (beyond 6 months)
        $safeCount = Stock::where('type', 'IN')
            ->where('availability', true)
            ->whereDate('expiryDate', '>', now()->addMonths(6)->startOfDay())
            ->whereNull('reason')
            ->update(['reason' => 'safe']);

        $this->info("Updated {$safeCount} safe products.");

        $this->info('Expiry status update completed!');
        
        return Command::SUCCESS;
    }
}