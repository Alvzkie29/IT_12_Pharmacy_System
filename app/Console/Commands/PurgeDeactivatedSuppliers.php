<?php

namespace App\Console\Commands;

use App\Models\Suppliers;
use Carbon\Carbon;
use Illuminate\Console\Command;

class PurgeDeactivatedSuppliers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'suppliers:purge-deactivated';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Permanently delete suppliers that have been deactivated for more than 3 months';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        
        // Find suppliers that are inactive and were updated more than 3 months ago
        $suppliers = Suppliers::where('is_active', false)
            ->where('updated_at', '<', $threeMonthsAgo)
            ->get();
            
        $count = 0;
        
        foreach ($suppliers as $supplier) {
            // Permanently delete the supplier
            $supplier->forceDelete();
            $count++;
        }
        
        $this->info("Permanently deleted {$count} suppliers that were deactivated for more than 3 months.");
        
        return Command::SUCCESS;
    }
}