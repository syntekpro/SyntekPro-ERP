<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ShopStock;
use App\Services\Settings\BusinessSettingsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosSaleController extends Controller
{
    public function index(Request $request, BusinessSettingsService $businessSettings): View
    {
        $user = $request->user();

        abort_unless($user !== null && $user->shop_id !== null, 403);

        $shopId = $user->shop_id;

        $products = Product::query()
            ->leftJoin('shop_stock as stock', function ($join) use ($shopId): void {
                $join->on('stock.product_id', '=', 'products.id')
                    ->where('stock.shop_id', '=', $shopId);
            })
            ->where('products.is_active', true)
            ->orderBy('products.name')
            ->get([
                'products.id',
                'products.name',
                'products.sku',
                'products.barcode',
                'products.price',
                'products.vat_rate',
                'products.is_excise_applicable',
                'products.excise_rate',
                'stock.quantity as local_stock',
            ])
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => (string) $product->price,
                'vat_rate' => number_format($businessSettings->vatRate(), 2, '.', ''),
                'is_excise_applicable' => (bool) $product->is_excise_applicable,
                'excise_rate' => $product->is_excise_applicable ? (string) ($product->excise_rate ?? '0.00') : '0.00',
                'local_stock' => (string) ($product->local_stock ?? '0'),
            ])
            ->values();

        $stock = ShopStock::query()
            ->where('shop_id', $shopId)
            ->get(['product_id', 'quantity'])
            ->mapWithKeys(fn (ShopStock $row) => [$row->product_id => (string) $row->quantity]);

        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'payment_terms_days'])
            ->map(fn (Customer $customer) => [
                'id' => $customer->id,
                'name' => $customer->name,
                'code' => $customer->code,
                'payment_terms_days' => $customer->payment_terms_days,
            ])
            ->values();

        return view('pos.sales', [
            'shop' => $user->shop,
            'cashier' => $user,
            'bootstrap' => [
                'sale_contract_version' => '2026-07-16',
                'tax' => [
                    'vat_enabled' => $businessSettings->vatEnabled(),
                    'vat_rate' => number_format($businessSettings->vatRate(), 2, '.', ''),
                ],
                'shop' => [
                    'id' => $user->shop?->id,
                    'name' => $user->shop?->name,
                    'slug' => $user->shop?->slug,
                ],
                'cashier' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'products' => $products,
                'shop_stock' => $stock,
                'customers' => $customers,
            ],
        ]);
    }
}
