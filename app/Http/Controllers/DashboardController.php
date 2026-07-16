<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\Warehouse;
use App\Support\ShopContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        return view('dashboard', [
            'counts' => [
                'shops' => Shop::query()->count(),
                'warehouses' => Warehouse::query()->count(),
                'products' => Product::query()->count(),
            ],
            'currentShopId' => ShopContext::shopId(),
            'user' => $request->user(),
        ]);
    }
}