<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrialBalanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isShopManager() || $user->isAccountant()), 403);

        $validated = $request->validate([
            'shop_id' => ['nullable', 'integer', 'exists:shops,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $shopId = $validated['shop_id'] ?? null;

        if ($user->isShopManager()) {
            $shopId = $user->shop_id;
        }

        $rows = Account::query()
            ->leftJoin('journal_entry_lines', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->leftJoin('journal_entries', function ($join) use ($shopId, $validated): void {
                $join->on('journal_entries.id', '=', 'journal_entry_lines.journal_entry_id');

                if ($shopId !== null) {
                    $join->where('journal_entries.shop_id', '=', $shopId);
                }

                if (($validated['start_date'] ?? null) !== null) {
                    $join->whereDate('journal_entries.entry_date', '>=', $validated['start_date']);
                }

                if (($validated['end_date'] ?? null) !== null) {
                    $join->whereDate('journal_entries.entry_date', '<=', $validated['end_date']);
                }
            })
            ->select([
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.account_type',
                DB::raw('COALESCE(SUM(CASE WHEN journal_entries.id IS NOT NULL THEN journal_entry_lines.debit ELSE 0 END), 0) as total_debit'),
                DB::raw('COALESCE(SUM(CASE WHEN journal_entries.id IS NOT NULL THEN journal_entry_lines.credit ELSE 0 END), 0) as total_credit'),
            ])
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.account_type')
            ->orderBy('accounts.code')
            ->get();

        $totalDebits = (float) $rows->sum('total_debit');
        $totalCredits = (float) $rows->sum('total_credit');

        return view('reports.trial-balance', [
            'rows' => $rows,
            'shops' => Shop::query()->orderBy('name')->get(),
            'filters' => [
                'shop_id' => $shopId,
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ],
            'isShopScopedUser' => $user->isShopManager(),
            'totalDebits' => $totalDebits,
            'totalCredits' => $totalCredits,
            'isBalanced' => round($totalDebits, 2) === round($totalCredits, 2),
        ]);
    }
}
