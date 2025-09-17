<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('products')->insert([
            [
                'supplierID'   => 1, // assumes supplier with ID 1 exists
                'productName'  => 'Amoxicillin 500mg',
                'price'        => 12.50,
                'category'     => 'Antibiotic',
                'description'  => 'Used for bacterial infections',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'supplierID'   => 1,
                'productName'  => 'Vitamin C 1000mg',
                'price'        => 5.00,
                'category'     => 'Vitamins',
                'description'  => 'Boosts immune system',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'supplierID'   => 2, // assumes supplier with ID 2 exists
                'productName'  => 'Paracetamol 500mg',
                'price'        => 3.00,
                'category'     => 'Analgesic',
                'description'  => 'Pain reliever and fever reducer',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}


