<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stock')->insert([
            [
                'productID'    => 1, // Amoxicillin
                'type'         => 'IN',
                'quantity'      => 100,
                'isAvailable'  => true,
                'batchNo'      => 1001,
                'expiryDate'   => '2026-12-31',
                'movementDate' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'productID'    => 2, // Vitamin C
                'type'         => 'IN',
                'quantity'      => 200,
                'isAvailable'  => true,
                'batchNo'      => 2001,
                'expiryDate'   => '2025-06-30',
                'movementDate' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
