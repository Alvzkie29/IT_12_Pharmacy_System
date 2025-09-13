<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportsController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
