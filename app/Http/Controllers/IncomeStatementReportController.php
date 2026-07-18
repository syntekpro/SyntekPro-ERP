<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use App\Services\Accounting\FinancialStatementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeStatementReportController extends Controller
{
    public function __construct(protected FinancialStatementService $financialStatementService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant() || $user->isShopManager()), 403);

        $validated = $request->validate([
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $shopId = $validated['shop_id'] ?? null;

        if ($user->isShopManager()) {
            $shopId = $user->shop_id;
        }

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();

        return view('reports.income-statement', [
            'statement' => $this->financialStatementService->incomeStatement($shopId, $startDate, $endDate),
            'shops' => Shop::query()->orderBy('name')->get(),
            'filters' => [
                'shop_id' => $shopId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'isShopScopedUser' => $user->isShopManager(),
        ]);
    }
}
