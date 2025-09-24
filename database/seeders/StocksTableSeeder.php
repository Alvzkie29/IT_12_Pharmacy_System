<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StocksTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('stocks')->insert([
            [
                'productID'    => 1, // Augmentin (Amoxicillin)
                'employeeID'   => 1, // assumes employee with ID 1 exists
                'type'         => 'IN',
                'price'        => 12.50,
                'quantity'     => 100,
                'availability' => true,
                'batchNo'      => 'AMX-1001',
                'expiryDate'   => '2026-12-31',
                'movementDate' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'productID'    => 2, // Ceelin (Ascorbic Acid)
                'employeeID'   => 1,
                'type'         => 'IN',
                'price'        => 5.00,
                'quantity'     => 200,
                'availability' => true,
                'batchNo'      => 'VITC-2001',
                'expiryDate'   => '2025-06-30',
                'movementDate' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'productID'    => 3, // Biogesic (Paracetamol)
                'employeeID'   => 1,
                'type'         => 'IN',
                'price'        => 3.00,
                'quantity'     => 300,
                'availability' => true,
                'batchNo'      => 'PCM-3001',
                'expiryDate'   => '2027-01-15',
                'movementDate' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
