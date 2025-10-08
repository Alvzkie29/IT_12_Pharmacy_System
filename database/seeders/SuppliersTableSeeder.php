<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersTableSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            [
                'supplierName' => 'Mercury Drug Distributors Inc.',
                'contactInfo' => '02-8888-0001',
                'address' => 'Quezon City, Metro Manila',
            ],
            [
                'supplierName' => 'United Laboratories (Unilab)',
                'contactInfo' => '02-8888-8888',
                'address' => 'Mandaluyong City, Metro Manila',
            ],
            [
                'supplierName' => 'Pfizer Philippines',
                'contactInfo' => '02-7777-7777',
                'address' => 'Bonifacio Global City, Taguig',
            ],
            [
                'supplierName' => 'GSK Pharmaceuticals',
                'contactInfo' => '02-9123-4567',
                'address' => 'Makati City, Metro Manila',
            ],
            [
                'supplierName' => 'RiteMed',
                'contactInfo' => '02-8989-1234',
                'address' => 'Pasig City, Metro Manila',
            ],
        ];

        foreach ($suppliers as &$supplier) {
            $supplier['created_at'] = now();
            $supplier['updated_at'] = now();
        }

        DB::table('suppliers')->insert($suppliers);
    }
}
