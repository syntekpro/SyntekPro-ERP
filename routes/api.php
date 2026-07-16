<?php

use App\Http\Controllers\Api\PosSaleSyncController;
use App\Http\Controllers\Api\AuthTokenController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens', [AuthTokenController::class, 'store'])->middleware('guest');

Route::middleware('auth:sanctum')->post('/pos/sync', [PosSaleSyncController::class, 'store']);

Route::middleware('auth:sanctum')->get('/user', function () {
    return request()->user();
});