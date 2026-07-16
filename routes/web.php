<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login')->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('shops', ShopController::class)->only(['index', 'create', 'edit']);
    Route::resource('warehouses', WarehouseController::class)->only(['index', 'create', 'edit']);
    Route::resource('products', ProductController::class)->only(['index', 'create', 'edit']);
    Route::resource('users', UserController::class)->only(['index', 'create', 'edit']);
    Route::resource('stock-transfers', StockTransferController::class)->only(['index', 'create']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
