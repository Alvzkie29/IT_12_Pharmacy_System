<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'supplierID'   => 1, // PharmaCorp
                'name'         => 'Amoxicillin',
                'price'        => 120.50,
                'category'     => 'Antibiotic',
                'description'  => 'Used to treat bacterial infections',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'supplierID'   => 2, // MediLife
                'name'         => 'Vitamin C',
                'price'        => 50.00,
                'category'     => 'Vitamins',
                'description'  => 'Boosts immune system',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
