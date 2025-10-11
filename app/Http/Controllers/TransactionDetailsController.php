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
    $query = Transaction::join('sales', 'transactions.saleID', '=', 'sales.saleID')
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
            'sales.cash_received', // Add this
            'sales.change_given',  // Add this
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
        );

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('products.productName', 'like', "%{$search}%")
                  ->orWhere('products.genericName', 'like', "%{$search}%")
                  ->orWhere('stocks.batchNo', 'like', "%{$search}%")
                  ->orWhere('transactions.transactionID', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->has('from_date') && !empty($request->from_date)) {
            $query->whereDate('transactions.created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $query->whereDate('transactions.created_at', '<=', $request->to_date);
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')->paginate(10);

        // Calculate totals for the footer - we need to get the totals from the same query
        $totalsQuery = Transaction::join('sales', 'transactions.saleID', '=', 'sales.saleID')
            ->join('stocks', 'transactions.stockID', '=', 'stocks.stockID')
            ->join('products', 'stocks.productID', '=', 'products.productID')
            ->select(
                DB::raw('SUM(transactions.quantity) as total_qty'),
                DB::raw('SUM(transactions.quantity * stocks.selling_price) as total_original'),
                DB::raw('SUM(CASE 
                    WHEN sales.isDiscounted = 1 
                    THEN (transactions.quantity * stocks.selling_price) * 0.8 
                    ELSE (transactions.quantity * stocks.selling_price) 
                END) as total_discounted'),
                DB::raw('SUM(CASE 
                    WHEN sales.isDiscounted = 1 
                    THEN (transactions.quantity * stocks.selling_price) * 0.2 
                    ELSE 0 
                END) as total_discount'),
                DB::raw('SUM(CASE 
                    WHEN sales.isDiscounted = 1 
                    THEN ((transactions.quantity * stocks.selling_price) * 0.8 - (stocks.purchase_price * transactions.quantity))
                    ELSE ((transactions.quantity * stocks.selling_price) - (stocks.purchase_price * transactions.quantity))
                END) as total_profit'),
                DB::raw('SUM(sales.cash_received) as total_cash_received'), // Add this
                DB::raw('SUM(sales.change_given) as total_change_given')    // Add this
            );

        // Apply the same filters to the totals query
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $totalsQuery->where(function($q) use ($search) {
                $q->where('products.productName', 'like', "%{$search}%")
                ->orWhere('products.genericName', 'like', "%{$search}%")
                ->orWhere('stocks.batchNo', 'like', "%{$search}%")
                ->orWhere('transactions.transactionID', 'like', "%{$search}%");
            });
        }

        if ($request->has('from_date') && !empty($request->from_date)) {
            $totalsQuery->whereDate('transactions.created_at', '>=', $request->from_date);
        }

        if ($request->has('to_date') && !empty($request->to_date)) {
            $totalsQuery->whereDate('transactions.created_at', '<=', $request->to_date);
        }

        $totalsResult = $totalsQuery->first();

        $totals = [
            'qty' => $totalsResult->total_qty ?? 0,
            'original_total' => $totalsResult->total_original ?? 0,
            'discounted_total' => $totalsResult->total_discounted ?? 0,
            'discount' => $totalsResult->total_discount ?? 0,
            'profit' => $totalsResult->total_profit ?? 0,
            'cash_received' => $totalsResult->total_cash_received ?? 0, // Add this
            'change_given' => $totalsResult->total_change_given ?? 0,   // Add this
        ];

        return view('reports.transaction_details', compact('transactions', 'totals'));
    }
}