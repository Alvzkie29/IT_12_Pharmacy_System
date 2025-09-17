<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('suppliers')->insert([
            [
                'supplierName' => 'MediPharma Distributors',
                'contactInfo'  => 'medipharma@example.com | +63 912 345 6789',
                'address'      => '123 Health St, Quezon City',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'supplierName' => 'HealthFirst Supplies',
                'contactInfo'  => 'healthfirst@example.com | +63 923 456 7890',
                'address'      => '456 Wellness Ave, Makati',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
            [
                'supplierName' => 'Global Pharma Imports',
                'contactInfo'  => 'globalpharma@example.com | +63 934 567 8901',
                'address'      => '789 Medical Blvd, Cebu City',
                'created_at'   => now(),
                'updated_at'   => now(),
            ],
        ]);
    }
}
