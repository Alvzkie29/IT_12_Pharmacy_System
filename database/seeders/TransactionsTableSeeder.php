<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('transactions')->insert([
            [
                'saleID'     => 1, // links to SalesTableSeeder (Pharmacist sale)
                'stockID'    => 1, // links to StocksTableSeeder (Amoxicillin batch)
                'quantity'   => 5,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
            [
                'saleID'     => 2, // links to SalesTableSeeder (Staff sale)
                'stockID'    => 2, // Vitamin C stock
                'quantity'   => 10,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
            [
                'saleID'     => 3, // links to SalesTableSeeder (Pharmacist sale today)
                'stockID'    => 3, // Paracetamol stock
                'quantity'   => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}

