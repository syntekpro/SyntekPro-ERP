<?php

namespace App\Services\Cheques;

use App\Enums\ChequeDirection;
use App\Enums\ChequeStatus;
use App\Enums\SalePaymentMethod;
use App\Enums\SupplierBillStatus;
use App\Models\Account;
use App\Models\Cheque;
use App\Models\Sale;
use App\Models\SupplierBill;
use App\Services\Accounting\JournalEntryService;
use Illuminate\Support\Facades\DB;

class ChequeService
{
    public function __construct(protected JournalEntryService $journalEntryService)
    {
    }

    public function recordIncomingForSale(
        int $saleId,
        float $amount,
        string $chequeNumber,
        string $bankName,
        string $chequeDate,
        ?int $userId = null,
    ): Cheque {
        return DB::transaction(function () use ($saleId, $amount, $chequeNumber, $bankName, $chequeDate, $userId): Cheque {
            $sale = Sale::query()->whereKey($saleId)->lockForUpdate()->firstOrFail();

            if ($sale->payment_method !== SalePaymentMethod::CreditAccount) {
                throw new \RuntimeException('Cheques can only be recorded for credit-account sales.');
            }

            $normalizedAmount = $this->normalizeAmount($amount);

            if ($normalizedAmount <= 0) {
                throw new \RuntimeException('Cheque amount must be greater than zero.');
            }

            if ($normalizedAmount > (float) $sale->outstanding_balance) {
                throw new \RuntimeException('Cheque amount cannot exceed the outstanding sale balance.');
            }

            $remaining = $this->normalizeAmount((float) $sale->outstanding_balance - $normalizedAmount);

            $cheque = Cheque::query()->create([
                'direction' => ChequeDirection::Incoming,
                'status' => ChequeStatus::Pending,
                'cheque_number' => trim($chequeNumber),
                'bank_name' => trim($bankName),
                'cheque_date' => $chequeDate,
                'amount' => $normalizedAmount,
                'sale_id' => $sale->id,
                'created_by' => $userId,
            ]);

            $sale->update(['outstanding_balance' => $remaining]);

            $pdcReceivable = $this->resolveRequiredAccount(config('accounting.cheques.pdc_receivable_account_code'));
            $accountsReceivable = $this->resolveRequiredAccount(config('accounting.receivables.accounts_receivable_account_code'));

            $journalEntry = $this->journalEntryService->create([
                'shop_id' => null,
                'entry_date' => $chequeDate,
                'reference' => 'AR-CHQ-'.$cheque->id,
                'description' => 'Incoming post-dated cheque recorded',
                'source' => 'cheque_recorded',
                'created_by' => $userId,
            ], [
                [
                    'account_id' => $pdcReceivable->id,
                    'debit' => $normalizedAmount,
                    'credit' => 0,
                    'description' => 'Post-dated cheque receivable',
                ],
                [
                    'account_id' => $accountsReceivable->id,
                    'debit' => 0,
                    'credit' => $normalizedAmount,
                    'description' => 'Reduce accounts receivable',
                ],
            ]);

            $cheque->update(['recorded_journal_entry_id' => $journalEntry->id]);

            return $cheque->fresh(['sale', 'recordedJournalEntry']);
        });
    }

    public function recordOutgoingForSupplierBill(
        int $supplierBillId,
        float $amount,
        string $chequeNumber,
        string $bankName,
        string $chequeDate,
        ?int $userId = null,
    ): Cheque {
        return DB::transaction(function () use ($supplierBillId, $amount, $chequeNumber, $bankName, $chequeDate, $userId): Cheque {
            $bill = SupplierBill::query()->whereKey($supplierBillId)->lockForUpdate()->firstOrFail();

            $normalizedAmount = $this->normalizeAmount($amount);

            if ($normalizedAmount <= 0) {
                throw new \RuntimeException('Cheque amount must be greater than zero.');
            }

            if ($normalizedAmount > (float) $bill->outstanding_balance) {
                throw new \RuntimeException('Cheque amount cannot exceed the outstanding bill balance.');
            }

            $remaining = $this->normalizeAmount((float) $bill->outstanding_balance - $normalizedAmount);

            $cheque = Cheque::query()->create([
                'direction' => ChequeDirection::Outgoing,
                'status' => ChequeStatus::Pending,
                'cheque_number' => trim($chequeNumber),
                'bank_name' => trim($bankName),
                'cheque_date' => $chequeDate,
                'amount' => $normalizedAmount,
                'supplier_bill_id' => $bill->id,
                'created_by' => $userId,
            ]);

            $bill->update([
                'outstanding_balance' => $remaining,
                'status' => $remaining <= 0 ? SupplierBillStatus::Paid : SupplierBillStatus::PartiallyPaid,
            ]);

            $accountsPayable = $this->resolveRequiredAccount(config('accounting.purchasing.accounts_payable_account_code'));
            $pdcPayable = $this->resolveRequiredAccount(config('accounting.cheques.pdc_payable_account_code'));

            $journalEntry = $this->journalEntryService->create([
                'shop_id' => null,
                'entry_date' => $chequeDate,
                'reference' => 'AP-CHQ-'.$cheque->id,
                'description' => 'Outgoing post-dated cheque recorded',
                'source' => 'cheque_recorded',
                'created_by' => $userId,
            ], [
                [
                    'account_id' => $accountsPayable->id,
                    'debit' => $normalizedAmount,
                    'credit' => 0,
                    'description' => 'Reduce accounts payable',
                ],
                [
                    'account_id' => $pdcPayable->id,
                    'debit' => 0,
                    'credit' => $normalizedAmount,
                    'description' => 'Post-dated cheque payable',
                ],
            ]);

            $cheque->update(['recorded_journal_entry_id' => $journalEntry->id]);

            return $cheque->fresh(['supplierBill', 'recordedJournalEntry']);
        });
    }

    public function markCleared(int $chequeId, ?int $userId = null, ?string $entryDate = null): Cheque
    {
        return DB::transaction(function () use ($chequeId, $userId, $entryDate): Cheque {
            $cheque = Cheque::query()->whereKey($chequeId)->lockForUpdate()->firstOrFail();

            $this->ensurePending($cheque, 'Only pending cheques can be cleared.');

            $date = $entryDate ?? now()->toDateString();
            $normalizedAmount = (float) $cheque->amount;

            if ($cheque->direction === ChequeDirection::Incoming) {
                $cashOrBank = $this->resolveRequiredAccount(config('accounting.receivables.payment_cash_or_bank_account_code'));
                $pdcReceivable = $this->resolveRequiredAccount(config('accounting.cheques.pdc_receivable_account_code'));

                $journalEntry = $this->journalEntryService->create([
                    'shop_id' => null,
                    'entry_date' => $date,
                    'reference' => 'AR-CHQ-CLR-'.$cheque->id,
                    'description' => 'Incoming post-dated cheque cleared',
                    'source' => 'cheque_cleared',
                    'created_by' => $userId,
                ], [
                    [
                        'account_id' => $cashOrBank->id,
                        'debit' => $normalizedAmount,
                        'credit' => 0,
                        'description' => 'Cash or bank receipt on cheque clearance',
                    ],
                    [
                        'account_id' => $pdcReceivable->id,
                        'debit' => 0,
                        'credit' => $normalizedAmount,
                        'description' => 'Clear post-dated cheque receivable',
                    ],
                ]);
            } else {
                $pdcPayable = $this->resolveRequiredAccount(config('accounting.cheques.pdc_payable_account_code'));
                $cashOrBank = $this->resolveRequiredAccount(config('accounting.purchasing.payment_cash_or_bank_account_code'));

                $journalEntry = $this->journalEntryService->create([
                    'shop_id' => null,
                    'entry_date' => $date,
                    'reference' => 'AP-CHQ-CLR-'.$cheque->id,
                    'description' => 'Outgoing post-dated cheque cleared',
                    'source' => 'cheque_cleared',
                    'created_by' => $userId,
                ], [
                    [
                        'account_id' => $pdcPayable->id,
                        'debit' => $normalizedAmount,
                        'credit' => 0,
                        'description' => 'Settle post-dated cheque payable',
                    ],
                    [
                        'account_id' => $cashOrBank->id,
                        'debit' => 0,
                        'credit' => $normalizedAmount,
                        'description' => 'Cash or bank outflow on cheque clearance',
                    ],
                ]);
            }

            $cheque->update([
                'status' => ChequeStatus::Cleared,
                'cleared_at' => $date,
                'cleared_journal_entry_id' => $journalEntry->id,
            ]);

            return $cheque->fresh(['clearedJournalEntry']);
        });
    }

    public function markBounced(int $chequeId, ?int $userId = null, ?string $entryDate = null): Cheque
    {
        return DB::transaction(function () use ($chequeId, $userId, $entryDate): Cheque {
            $cheque = Cheque::query()->whereKey($chequeId)->lockForUpdate()->firstOrFail();

            $this->ensurePending($cheque, 'Only pending cheques can be bounced.');

            $date = $entryDate ?? now()->toDateString();
            $normalizedAmount = (float) $cheque->amount;

            if ($cheque->direction === ChequeDirection::Incoming) {
                $sale = Sale::query()->whereKey($cheque->sale_id)->lockForUpdate()->firstOrFail();

                $accountsReceivable = $this->resolveRequiredAccount(config('accounting.receivables.accounts_receivable_account_code'));
                $pdcReceivable = $this->resolveRequiredAccount(config('accounting.cheques.pdc_receivable_account_code'));

                $journalEntry = $this->journalEntryService->create([
                    'shop_id' => null,
                    'entry_date' => $date,
                    'reference' => 'AR-CHQ-BNC-'.$cheque->id,
                    'description' => 'Incoming post-dated cheque bounced',
                    'source' => 'cheque_bounced',
                    'created_by' => $userId,
                ], [
                    [
                        'account_id' => $accountsReceivable->id,
                        'debit' => $normalizedAmount,
                        'credit' => 0,
                        'description' => 'Restore accounts receivable after bounce',
                    ],
                    [
                        'account_id' => $pdcReceivable->id,
                        'debit' => 0,
                        'credit' => $normalizedAmount,
                        'description' => 'Reverse post-dated cheque receivable',
                    ],
                ]);

                $sale->update([
                    'outstanding_balance' => $this->normalizeAmount((float) $sale->outstanding_balance + $normalizedAmount),
                ]);
            } else {
                $bill = SupplierBill::query()->whereKey($cheque->supplier_bill_id)->lockForUpdate()->firstOrFail();

                $pdcPayable = $this->resolveRequiredAccount(config('accounting.cheques.pdc_payable_account_code'));
                $accountsPayable = $this->resolveRequiredAccount(config('accounting.purchasing.accounts_payable_account_code'));

                $journalEntry = $this->journalEntryService->create([
                    'shop_id' => null,
                    'entry_date' => $date,
                    'reference' => 'AP-CHQ-BNC-'.$cheque->id,
                    'description' => 'Outgoing post-dated cheque bounced',
                    'source' => 'cheque_bounced',
                    'created_by' => $userId,
                ], [
                    [
                        'account_id' => $pdcPayable->id,
                        'debit' => $normalizedAmount,
                        'credit' => 0,
                        'description' => 'Reverse post-dated cheque payable',
                    ],
                    [
                        'account_id' => $accountsPayable->id,
                        'debit' => 0,
                        'credit' => $normalizedAmount,
                        'description' => 'Restore accounts payable after bounce',
                    ],
                ]);

                $restoredOutstanding = $this->normalizeAmount((float) $bill->outstanding_balance + $normalizedAmount);
                $billTotal = (float) $bill->total;

                $status = SupplierBillStatus::PartiallyPaid;

                if ($restoredOutstanding <= 0) {
                    $status = SupplierBillStatus::Paid;
                } elseif ($restoredOutstanding >= $billTotal) {
                    $status = SupplierBillStatus::Open;
                }

                $bill->update([
                    'outstanding_balance' => $restoredOutstanding,
                    'status' => $status,
                ]);
            }

            $cheque->update([
                'status' => ChequeStatus::Bounced,
                'bounced_at' => $date,
                'bounced_journal_entry_id' => $journalEntry->id,
            ]);

            return $cheque->fresh(['bouncedJournalEntry']);
        });
    }

    protected function ensurePending(Cheque $cheque, string $message): void
    {
        if ($cheque->status !== ChequeStatus::Pending) {
            throw new \RuntimeException($message);
        }
    }

    protected function resolveRequiredAccount(?string $code): Account
    {
        $account = Account::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($account === null) {
            throw new \RuntimeException("Required cheque account with code [{$code}] is missing or inactive.");
        }

        return $account;
    }

    protected function normalizeAmount(float $amount): float
    {
        return round($amount, 2);
    }
}
