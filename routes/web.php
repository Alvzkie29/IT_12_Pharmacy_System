<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SuppliersController;
use Illuminate\Support\Facades\Route;

// Landing page = Login form
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard (protected by auth middleware)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

Route::resource('sales', SaleController::class)->only(['index', 'store']);
Route::post('/sales/update-cart', [SaleController::class, 'updateCart'])->name('sales.updateCart');
Route::post('/sales/confirm', [SaleController::class, 'confirm'])->name('sales.confirm'); 
Route::post('/sales/finalize', [SaleController::class, 'finalize'])->name('sales.finalize'); 

Route::resource('suppliers', SuppliersController::class);

Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('/reports/print/{date}', [ReportsController::class, 'print'])->name('reports.print');

Route::post('/products', [ProductController::class, 'store'])->name('products.store');

Route::resource('products', ProductController::class)->only(['index','store', 'update', 'destroy']);


Route::prefix('inventory')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stockIn');
    Route::put('/inventory/stock-out/{id}', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');
});
