<?php

namespace App\Http\Controllers;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SaleStatus;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\SupplierBill;
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
                'active_shops' => Shop::query()->where('is_active', true)->count(),
                'warehouses' => Warehouse::query()->count(),
                'products' => Product::query()->count(),
                'open_purchase_orders' => PurchaseOrder::query()
                    ->whereNotIn('status', [
                        PurchaseOrderStatus::Received->value,
                        PurchaseOrderStatus::Closed->value,
                    ])->count(),
            ],
            'financials' => [
                'ar_outstanding' => (float) Sale::query()
                    ->where('outstanding_balance', '>', 0)
                    ->sum('outstanding_balance'),
                'ap_outstanding' => (float) SupplierBill::query()
                    ->where('outstanding_balance', '>', 0)
                    ->sum('outstanding_balance'),
                'todays_sales' => (float) Sale::query()
                    ->whereDate('sold_at', now()->toDateString())
                    ->whereNotIn('status', [
                        SaleStatus::Duplicate->value,
                        SaleStatus::Rejected->value,
                    ])
                    ->sum('total'),
            ],
            'currentShopId' => ShopContext::shopId(),
            'user' => $request->user(),
        ]);
    }
}