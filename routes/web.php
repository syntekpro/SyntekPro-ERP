<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\PosSaleController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\TrialBalanceReportController;
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
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/trial-balance', [TrialBalanceReportController::class, 'index'])->name('reports.trial-balance');
    Route::get('/pos/sales', [PosSaleController::class, 'index'])->name('pos.sales');
    Route::resource('shops', ShopController::class)->only(['index', 'create', 'edit']);
    Route::resource('warehouses', WarehouseController::class)->only(['index', 'create', 'edit']);
    Route::resource('products', ProductController::class)->only(['index', 'create', 'edit']);
    Route::resource('users', UserController::class)->only(['index', 'create', 'edit']);
    Route::resource('accounts', AccountController::class)->only(['index', 'create', 'edit']);
    Route::resource('journal-entries', JournalEntryController::class)->only(['index', 'create']);
    Route::resource('stock-transfers', StockTransferController::class)->only(['index', 'create']);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
