<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionDetailsController extends Controller
{
    public function index(Request $request)
    {
        // Use the Transaction model as the base and join with related tables
        $transactions = Transaction::join('sales', 'transactions.saleID', '=', 'sales.saleID')
            ->join('stocks', 'transactions.stockID', '=', 'stocks.stockID')
            ->join('products', 'stocks.productID', '=', 'products.productID')
            ->select(
                'transactions.transactionID',
                'transactions.created_at as transaction_date',
                'products.productName as product_name',
                'products.genericName as generic_name',
                'stocks.batchNo as batch_number',
                'transactions.quantity as qty',
                'stocks.purchase_price',
                'stocks.selling_price',
                DB::raw('(transactions.quantity * stocks.selling_price) as original_total'),
                DB::raw('CASE 
                    WHEN sales.isDiscounted = 1 
                    THEN (transactions.quantity * stocks.selling_price) * 0.8 
                    ELSE (transactions.quantity * stocks.selling_price) 
                END as discounted_total'),
                DB::raw('CASE 
                    WHEN sales.isDiscounted = 1 
                    THEN (transactions.quantity * stocks.selling_price) * 0.2 
                    ELSE 0 
                END as discount'),
                DB::raw('CASE 
                    WHEN sales.isDiscounted = 1 
                    THEN ((transactions.quantity * stocks.selling_price) * 0.8 - (stocks.purchase_price * transactions.quantity))
                    ELSE ((transactions.quantity * stocks.selling_price) - (stocks.purchase_price * transactions.quantity))
                END as profit')
            )
            ->orderBy('transactions.created_at', 'desc')
            ->paginate(10);

        // Calculate totals for the footer
        $totals = [
            'qty' => $transactions->sum('qty'),
            'original_total' => $transactions->sum('original_total'),
            'discounted_total' => $transactions->sum('discounted_total'),
            'discount' => $transactions->sum('discount'),
            'profit' => $transactions->sum('profit')
        ];

        return view('reports.transaction_details', compact('transactions', 'totals'));
    }
}