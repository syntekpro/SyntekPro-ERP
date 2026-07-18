<?php

namespace App\Http\Controllers;

use App\Services\Accounting\FinancialStatementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CashFlowReportController extends Controller
{
    public function __construct(protected FinancialStatementService $financialStatementService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant()), 403);

        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();

        return view('reports.cash-flow', [
            'statement' => $this->financialStatementService->cashFlowIndirect($startDate, $endDate),
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }
}
