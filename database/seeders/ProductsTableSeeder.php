<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'supplierID'   => 1, // assumes supplier with ID 1 exists
                'productName'  => 'Augmentin',         // brand name
                'genericName'  => 'Amoxicillin',       // generic name
                'productWeight'=> '500mg',
                'dosageForm'   => 'Capsule',
                'category'     => 'Antibiotic',
                'description'  => 'Broad-spectrum antibiotic for bacterial infections',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'supplierID'   => 1,
                'productName'  => 'Ceelin',            // brand name
                'genericName'  => 'Ascorbic Acid',     // generic name
                'productWeight'=> '1000mg/5ml',
                'dosageForm'   => 'Syrup',
                'category'     => 'Vitamins',
                'description'  => 'Vitamin C supplement to boost immune system',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'supplierID'   => 2, // assumes supplier with ID 2 exists
                'productName'  => 'Biogesic',          // brand name
                'genericName'  => 'Paracetamol',       // generic name
                'productWeight'=> '500mg',
                'dosageForm'   => 'Tablet',
                'category'     => 'Analgesic',
                'description'  => 'Pain reliever and fever reducer',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
