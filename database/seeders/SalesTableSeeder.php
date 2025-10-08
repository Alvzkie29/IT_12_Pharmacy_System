<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesTableSeeder extends Seeder
{
    public function run(): void
    {
        $employees = DB::table('employees')->pluck('employeeID')->toArray();
        $stocks = DB::table('stocks')->where('availability', true)->get();

        $sales = [];
        $transactions = [];

        // Generate ~20 sales across the last 7 days
        for ($i = 0; $i < 20; $i++) {
            $employeeID = $employees[array_rand($employees)];
            $daysAgo = rand(0, 6);
            $saleDate = Carbon::now()
                ->subDays($daysAgo)
                ->setTime(rand(8, 20), rand(0, 59), rand(0, 59));

            $itemsInSale = rand(1, 3);
            $subtotal = 0;

            for ($j = 0; $j < $itemsInSale; $j++) {
                $stock = $stocks->random();
                $currentStock = DB::table('stocks')->where('stockID', $stock->stockID)->first();

                if (!$currentStock || $currentStock->quantity <= 0) continue;

                $quantity = rand(1, max(1, $currentStock->quantity));
                $subtotal += $currentStock->selling_price * $quantity;

                // Deduct from stock
                DB::table('stocks')->where('stockID', $currentStock->stockID)->update([
                    'quantity' => $currentStock->quantity - $quantity,
                    'availability' => ($currentStock->quantity - $quantity) > 0,
                    'updated_at' => now(),
                ]);

                $transactions[] = [
                    'stockID'    => $currentStock->stockID,
                    'quantity'   => $quantity,
                    'created_at' => $saleDate,
                    'updated_at' => $saleDate,
                    '_saleIndex' => $i,
                ];
            }

            if ($subtotal > 0) {
                // ✅ 25% of sales have a fixed 20% discount
                $hasDiscount = rand(1, 100) <= 25;
                $discountAmount = $hasDiscount ? round($subtotal * 0.20, 2) : 0;
                $totalAmount = $subtotal - $discountAmount;

                $sales[] = [
                    'employeeID'      => $employeeID,
                    'totalAmount'     => $totalAmount,
                    'subtotal'        => $subtotal,
                    'discountAmount'  => $discountAmount,
                    'isDiscounted'    => $hasDiscount,
                    'saleDate'        => $saleDate,
                    'created_at'      => $saleDate,
                    'updated_at'      => $saleDate,
                ];
            }
        }

        // Insert sales
        DB::table('sales')->insert($sales);

        // Attach transactions to sale IDs
        $saleIDs = DB::table('sales')->orderBy('saleID')->pluck('saleID')->toArray();

        foreach ($transactions as &$t) {
            $t['saleID'] = $saleIDs[$t['_saleIndex']] ?? null;
            unset($t['_saleIndex']);
        }

        $transactions = array_filter($transactions, fn($t) => !is_null($t['saleID']));
        DB::table('transactions')->insert($transactions);
    }
}
