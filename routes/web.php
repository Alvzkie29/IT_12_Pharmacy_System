<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\AuthController;


use Illuminate\Support\Facades\Route;

// Landing page = Login form
Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard (protected by auth middleware)
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/sales', [SaleController::class, 'index'])->name('sales.index');
Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');

// INVENTORY CONTROLLERS
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::get('/inventory/create', [InventoryController::class, 'create'])->name('inventory.create');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::get('/inventory/edit/{id}', [InventoryController::class, 'edit'])->name('inventory.edit');
Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');