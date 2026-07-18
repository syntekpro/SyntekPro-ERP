<?php

namespace App\Http\Controllers;

use App\Services\Accounting\FinancialStatementService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BalanceSheetReportController extends Controller
{
    public function __construct(protected FinancialStatementService $financialStatementService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant()), 403);

        $validated = $request->validate([
            'as_of_date' => ['nullable', 'date'],
        ]);

        $asOfDate = $validated['as_of_date'] ?? now()->toDateString();

        return view('reports.balance-sheet', [
            'statement' => $this->financialStatementService->balanceSheet($asOfDate),
            'filters' => [
                'as_of_date' => $asOfDate,
            ],
        ]);
    }
}
