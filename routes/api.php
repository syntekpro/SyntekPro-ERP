<?php

use App\Http\Controllers\Api\AuthTokenController;
use Illuminate\Support\Facades\Route;

Route::post('/tokens', [AuthTokenController::class, 'store'])->middleware('guest');

Route::middleware('auth:sanctum')->get('/user', function () {
    return request()->user();
});