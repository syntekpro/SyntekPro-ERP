<?php

namespace App\Http\Controllers;

use App\Support\ShopContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('dashboard', [
            'currentShopId' => ShopContext::shopId(),
            'user' => $request->user(),
        ]);
    }
}