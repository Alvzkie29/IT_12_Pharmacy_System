<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('suppliers')->insert([
            [
                'supplierName' => 'PharmaCorp',
                'contactInfo'  => '09171234567',
                'address'      => '123 Main St, Manila',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'supplierName' => 'MediLife',
                'contactInfo'  => '09179876543',
                'address'      => '456 Health Ave, Cebu',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
