<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;


use Illuminate\Support\Facades\Route;

// Landing page = Login form
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard (protected by auth middleware)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');


Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
Route::get('/reports/print/{date}', [ReportsController::class, 'print'])->name('reports.print');

Route::post('/products', [ProductController::class, 'store'])->name('products.store');

Route::resource('products', ProductController::class)->only(['index','store']);


Route::prefix('inventory')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/stock-in', [InventoryController::class, 'stockIn'])->name('inventory.stockIn');
    Route::put('/inventory/stock-out/{id}', [InventoryController::class, 'stockOut'])->name('inventory.stockOut');
});
