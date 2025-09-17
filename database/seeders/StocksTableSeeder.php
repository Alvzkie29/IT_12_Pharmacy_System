<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StocksTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('stocks')->insert([
            [
                'productID'    => 1, // Amoxicillin
                'employeeID'   => 1, // Owner
                'type'         => 'IN',
                'price'        => 10.00,
                'quantity'     => 100,
                'availability' => true,
                'batchNo'      => 'AMX-2025-01',
                'expiryDate'   => '2026-12-31',
                'movementDate' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'productID'    => 2, // Vitamin C
                'employeeID'   => 2, // Pharmacist
                'type'         => 'IN',
                'price'        => 4.50,
                'quantity'     => 200,
                'availability' => true,
                'batchNo'      => 'VTC-2025-01',
                'expiryDate'   => '2025-12-31',
                'movementDate' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'productID'    => 3, // Paracetamol
                'employeeID'   => 3, // Staff
                'type'         => 'IN',
                'price'        => 2.50,
                'quantity'     => 300,
                'availability' => true,
                'batchNo'      => 'PCM-2025-01',
                'expiryDate'   => '2027-06-30',
                'movementDate' => now(),
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}

