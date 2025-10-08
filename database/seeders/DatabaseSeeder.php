<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\EmployeesTableSeeder;
use Database\Seeders\SuppliersTableSeeder;
use Database\Seeders\ProductsTableSeeder;
use Database\Seeders\StocksTableSeeder;
use Database\Seeders\SalesTableSeeder;
use Database\Seeders\TransactionsTableSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SuppliersTableSeeder::class,
            EmployeesTableSeeder::class,
            ProductsTableSeeder::class,
            StocksTableSeeder::class,
            SalesTableSeeder::class,
        ]);
    }
}
