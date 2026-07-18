<?php

namespace App\Services\Accounting;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinancialStatementService
{
    public function balanceSheet(?string $asOfDate = null): array
    {
        $asOfDate = $asOfDate ?? now()->toDateString();

        $rows = $this->accountBalancesUntil($asOfDate);

        $assets = $rows->where('account_type', AccountType::Asset->value)
            ->map(fn (array $row) => [
                ...$row,
                'balance' => $row['debit'] - $row['credit'],
            ])
            ->values();

        $liabilities = $rows->where('account_type', AccountType::Liability->value)
            ->map(fn (array $row) => [
                ...$row,
                'balance' => $row['credit'] - $row['debit'],
            ])
            ->values();

        $equity = $rows->where('account_type', AccountType::Equity->value)
            ->map(fn (array $row) => [
                ...$row,
                'balance' => $row['credit'] - $row['debit'],
            ])
            ->values();

        $netIncomeToDate = $this->netIncome(null, Carbon::parse($asOfDate)->startOfYear()->toDateString(), $asOfDate);

        $equity = $equity->push([
            'code' => 'CURR-EARN',
            'name' => 'Current Period Earnings',
            'account_type' => AccountType::Equity->value,
            'debit' => 0,
            'credit' => 0,
            'balance' => $netIncomeToDate,
        ]);

        $assetTotal = (float) $assets->sum('balance');
        $liabilityTotal = (float) $liabilities->sum('balance');
        $equityTotal = (float) $equity->sum('balance');

        return [
            'as_of_date' => $asOfDate,
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'asset_total' => $assetTotal,
            'liability_total' => $liabilityTotal,
            'equity_total' => $equityTotal,
            'is_balanced' => round($assetTotal, 2) === round($liabilityTotal + $equityTotal, 2),
        ];
    }

    public function incomeStatement(?int $shopId, string $startDate, string $endDate): array
    {
        $rows = $this->accountBalancesRange($startDate, $endDate, $shopId);

        $revenueRows = $rows->where('account_type', AccountType::Revenue->value)
            ->map(fn (array $row) => [
                ...$row,
                'amount' => $row['credit'] - $row['debit'],
            ])
            ->values();

        $expenseRows = $rows->where('account_type', AccountType::Expense->value)
            ->map(fn (array $row) => [
                ...$row,
                'amount' => $row['debit'] - $row['credit'],
            ])
            ->values();

        $cogsCode = (string) config('accounting.pos.cogs_account_code', '5100');

        $cogsRows = $expenseRows->where('code', $cogsCode)->values();
        $operatingExpenseRows = $expenseRows->where('code', '!=', $cogsCode)->values();

        $revenueTotal = (float) $revenueRows->sum('amount');
        $cogsTotal = (float) $cogsRows->sum('amount');
        $operatingExpenseTotal = (float) $operatingExpenseRows->sum('amount');
        $grossProfit = $revenueTotal - $cogsTotal;
        $netIncome = $grossProfit - $operatingExpenseTotal;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'shop_id' => $shopId,
            'revenue_rows' => $revenueRows,
            'cogs_rows' => $cogsRows,
            'operating_expense_rows' => $operatingExpenseRows,
            'revenue_total' => $revenueTotal,
            'cogs_total' => $cogsTotal,
            'gross_profit' => $grossProfit,
            'operating_expense_total' => $operatingExpenseTotal,
            'net_income' => $netIncome,
        ];
    }

    public function cashFlowIndirect(string $startDate, string $endDate): array
    {
        $netIncome = $this->netIncome(null, $startDate, $endDate);

        $codes = [
            'cash' => (string) config('accounting.pos.cash_account_code', '1010'),
            'bank' => '1020',
            'ar' => (string) config('accounting.receivables.accounts_receivable_account_code', '1100'),
            'inventory' => (string) config('accounting.purchasing.inventory_account_code', '1200'),
            'vat_receivable' => (string) config('accounting.purchasing.input_vat_receivable_account_code', '1300'),
            'ap' => (string) config('accounting.purchasing.accounts_payable_account_code', '2100'),
            'vat_payable' => (string) config('accounting.pos.vat_payable_account_code', '2200'),
        ];

        $startMinusOne = Carbon::parse($startDate)->subDay()->toDateString();

        $startBalances = $this->balancesByCode($codes, $startMinusOne);
        $endBalances = $this->balancesByCode($codes, $endDate);

        $deltaAr = $endBalances['ar'] - $startBalances['ar'];
        $deltaInventory = $endBalances['inventory'] - $startBalances['inventory'];
        $deltaVatReceivable = $endBalances['vat_receivable'] - $startBalances['vat_receivable'];
        $deltaAp = $endBalances['ap'] - $startBalances['ap'];
        $deltaVatPayable = $endBalances['vat_payable'] - $startBalances['vat_payable'];

        $workingCapitalAdjustments = [
            'ar' => -$deltaAr,
            'inventory' => -$deltaInventory,
            'vat_receivable' => -$deltaVatReceivable,
            'ap' => $deltaAp,
            'vat_payable' => $deltaVatPayable,
        ];

        $operatingCashFlow = $netIncome + array_sum($workingCapitalAdjustments);

        $cashOpening = $startBalances['cash'] + $startBalances['bank'];
        $cashClosing = $endBalances['cash'] + $endBalances['bank'];

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'net_income' => $netIncome,
            'working_capital_adjustments' => $workingCapitalAdjustments,
            'operating_cash_flow' => $operatingCashFlow,
            'investing_cash_flow' => 0.0,
            'financing_cash_flow' => 0.0,
            'net_cash_change' => $operatingCashFlow,
            'cash_opening' => $cashOpening,
            'cash_closing' => $cashClosing,
            'cash_change_actual' => $cashClosing - $cashOpening,
        ];
    }

    public function netIncome(?int $shopId, string $startDate, string $endDate): float
    {
        $statement = $this->incomeStatement($shopId, $startDate, $endDate);

        return (float) $statement['net_income'];
    }

    protected function accountBalancesUntil(string $asOfDate): Collection
    {
        return $this->buildAccountBalancesQuery(null, $asOfDate, null)
            ->get()
            ->map(fn ($row) => [
                'code' => $row->code,
                'name' => $row->name,
                'account_type' => (string) $row->account_type,
                'debit' => (float) $row->debit_sum,
                'credit' => (float) $row->credit_sum,
            ]);
    }

    protected function accountBalancesRange(string $startDate, string $endDate, ?int $shopId): Collection
    {
        return $this->buildAccountBalancesQuery($startDate, $endDate, $shopId)
            ->get()
            ->map(fn ($row) => [
                'code' => $row->code,
                'name' => $row->name,
                'account_type' => (string) $row->account_type,
                'debit' => (float) $row->debit_sum,
                'credit' => (float) $row->credit_sum,
            ]);
    }

    protected function buildAccountBalancesQuery(?string $startDate, string $endDate, ?int $shopId)
    {
        return Account::query()
            ->leftJoin('journal_entry_lines', 'journal_entry_lines.account_id', '=', 'accounts.id')
            ->leftJoin('journal_entries', function ($join) use ($startDate, $endDate, $shopId): void {
                $join->on('journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
                    ->whereDate('journal_entries.entry_date', '<=', $endDate);

                if ($startDate !== null) {
                    $join->whereDate('journal_entries.entry_date', '>=', $startDate);
                }

                if ($shopId !== null) {
                    $join->where('journal_entries.shop_id', '=', $shopId);
                }
            })
            ->selectRaw('accounts.code, accounts.name, accounts.account_type, COALESCE(SUM(CASE WHEN journal_entries.id IS NOT NULL THEN journal_entry_lines.debit ELSE 0 END), 0) as debit_sum, COALESCE(SUM(CASE WHEN journal_entries.id IS NOT NULL THEN journal_entry_lines.credit ELSE 0 END), 0) as credit_sum')
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.account_type')
            ->orderBy('accounts.code');
    }

    protected function balancesByCode(array $codes, string $asOfDate): array
    {
        $rows = $this->accountBalancesUntil($asOfDate)->keyBy('code');

        $readAsset = function (string $code) use ($rows): float {
            $row = $rows->get($code);

            if (! $row) {
                return 0.0;
            }

            return (float) $row['debit'] - (float) $row['credit'];
        };

        $readLiability = function (string $code) use ($rows): float {
            $row = $rows->get($code);

            if (! $row) {
                return 0.0;
            }

            return (float) $row['credit'] - (float) $row['debit'];
        };

        return [
            'cash' => $readAsset($codes['cash']),
            'bank' => $readAsset($codes['bank']),
            'ar' => $readAsset($codes['ar']),
            'inventory' => $readAsset($codes['inventory']),
            'vat_receivable' => $readAsset($codes['vat_receivable']),
            'ap' => $readLiability($codes['ap']),
            'vat_payable' => $readLiability($codes['vat_payable']),
        ];
    }
}
