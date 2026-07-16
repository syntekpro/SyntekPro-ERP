<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isShopManager()), 403);

        $validated = $request->validate([
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $shopId = $validated['shop_id'] ?? null;

        if ($user->isShopManager()) {
            $shopId = $user->shop_id;
        }

        return view('reports.index', [
            'filters' => [
                'shop_id' => $shopId,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ],
            'shops' => Shop::query()->orderBy('name')->get(),
            'vatRows' => $this->buildVatReport($shopId, $validated['start_date'] ?? null, $validated['end_date'] ?? null),
            'marginRows' => $this->buildMarginReport($shopId, $validated['start_date'] ?? null, $validated['end_date'] ?? null),
            'fastMovingRows' => $this->buildFastMovingReport($shopId, $validated['start_date'] ?? null, $validated['end_date'] ?? null),
            'isShopScopedUser' => $user->isShopManager(),
        ]);
    }

    protected function applySaleFilters(Builder $query, ?int $shopId, ?string $startDate, ?string $endDate): Builder
    {
        return $query
            ->where('status', 'synced')
            ->when($shopId !== null, fn (Builder $inner) => $inner->where('shop_id', $shopId))
            ->when($startDate !== null, fn (Builder $inner) => $inner->whereDate('sold_at', '>=', $startDate))
            ->when($endDate !== null, fn (Builder $inner) => $inner->whereDate('sold_at', '<=', $endDate));
    }

    protected function buildVatReport(?int $shopId, ?string $startDate, ?string $endDate)
    {
        return $this->applySaleFilters(Sale::query(), $shopId, $startDate, $endDate)
            ->join('shops', 'shops.id', '=', 'sales.shop_id')
            ->select([
                'sales.shop_id',
                'shops.name as shop_name',
                DB::raw('SUM(sales.vat_total) as vat_total'),
                DB::raw('SUM(sales.total) as gross_total'),
                DB::raw('COUNT(sales.id) as sale_count'),
            ])
            ->groupBy('sales.shop_id', 'shops.name')
            ->orderBy('shops.name')
            ->get();
    }

    protected function buildMarginReport(?int $shopId, ?string $startDate, ?string $endDate)
    {
        $saleIds = $this->applySaleFilters(Sale::query(), $shopId, $startDate, $endDate)->select('id');

        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('shops', 'shops.id', '=', 'sales.shop_id')
            ->whereIn('sale_items.sale_id', $saleIds)
            ->select([
                'sales.shop_id',
                'shops.name as shop_name',
                'sale_items.product_id',
                'sale_items.product_name',
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as revenue_ex_vat'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as cogs_total'),
                DB::raw('SUM((sale_items.quantity * sale_items.unit_price) - (sale_items.quantity * sale_items.unit_cost)) as margin_total'),
            ])
            ->groupBy('sales.shop_id', 'shops.name', 'sale_items.product_id', 'sale_items.product_name')
            ->orderByDesc('margin_total')
            ->get();
    }

    protected function buildFastMovingReport(?int $shopId, ?string $startDate, ?string $endDate)
    {
        $saleIds = $this->applySaleFilters(Sale::query(), $shopId, $startDate, $endDate)->select('id');

        return SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('shops', 'shops.id', '=', 'sales.shop_id')
            ->whereIn('sale_items.sale_id', $saleIds)
            ->select([
                'sales.shop_id',
                'shops.name as shop_name',
                'sale_items.product_id',
                'sale_items.product_name',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
                DB::raw('SUM(sale_items.line_total) as line_total_sum'),
            ])
            ->groupBy('sales.shop_id', 'shops.name', 'sale_items.product_id', 'sale_items.product_name')
            ->orderByDesc('quantity_sold')
            ->limit(50)
            ->get();
    }
}
