<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('sales')->insert([
            [
                'employeeID'  => 2, // Pharmacist handled the sale
                'totalAmount' => 150.00,
                'saleDate'    => now()->subDays(2),
                'created_at'  => now()->subDays(2),
                'updated_at'  => now()->subDays(2),
            ],
            [
                'employeeID'  => 3, // Staff handled the sale
                'totalAmount' => 75.00,
                'saleDate'    => now()->subDay(),
                'created_at'  => now()->subDay(),
                'updated_at'  => now()->subDay(),
            ],
            [
                'employeeID'  => 2,
                'totalAmount' => 200.00,
                'saleDate'    => now(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}