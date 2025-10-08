<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StocksTableSeeder extends Seeder
{
    public function run(): void
    {
        $products = DB::table('products')->pluck('productID')->toArray();
        $employees = DB::table('employees')->pluck('employeeID')->toArray();

        $stocks = [];

        foreach ($products as $productID) {
            $batchCount = rand(2, 4);
            $usedBatches = [];

            for ($i = 0; $i < $batchCount; $i++) {
                do {
                    $batchNo = 'BCH' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                    $expiryDate = Carbon::now()->addMonths(rand(6, 30))->format('Y-m-d');
                    $key = $batchNo . '-' . $expiryDate;
                } while (in_array($key, $usedBatches));

                $usedBatches[] = $key;

                $purchase_price = rand(5, 10);
                $selling_price = $purchase_price + rand(5, 10);

                // Random initial quantity
                $quantity = rand(40, 120);

                // Random depletion (simulate previous sales)
                $sold = rand(0, (int)($quantity * 0.6)); // 0–60% already sold
                $remaining = $quantity - $sold;

                $stocks[] = [
                    'productID'     => $productID,
                    'employeeID'    => $employees[array_rand($employees)],
                    'type'          => 'IN',
                    'purchase_price'=> $purchase_price,
                    'selling_price' => $selling_price,
                    'quantity'      => $remaining,
                    'availability'  => $remaining > 0,
                    'batchNo'       => $batchNo,
                    'expiryDate'    => $expiryDate,
                    'movementDate'  => Carbon::now()->subDays(rand(10, 90))->format('Y-m-d'),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        DB::table('stocks')->insert($stocks);
    }
}
