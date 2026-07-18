<?php

namespace App\Services\Receivables;

use App\Enums\SalePaymentMethod;
use App\Models\CustomerPayment;
use App\Services\Accounting\PostsCustomerPaymentToLedger;
use Illuminate\Support\Facades\DB;

class CustomerPaymentService
{
    public function __construct(protected PostsCustomerPaymentToLedger $postsCustomerPaymentToLedger)
    {
    }

    public function record(int $saleId, float $amount, string $paidAt, ?string $reference, ?string $notes, ?int $userId = null): CustomerPayment
    {
        return DB::transaction(function () use ($saleId, $amount, $paidAt, $reference, $notes, $userId): CustomerPayment {
            $sale = \App\Models\Sale::query()
                ->whereKey($saleId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($sale->payment_method !== SalePaymentMethod::CreditAccount) {
                throw new \RuntimeException('Payments can only be recorded for credit-account sales.');
            }

            $normalizedAmount = round($amount, 2);

            if ($normalizedAmount <= 0) {
                throw new \RuntimeException('Payment amount must be greater than zero.');
            }

            if ($normalizedAmount > (float) $sale->outstanding_balance) {
                throw new \RuntimeException('Payment amount cannot exceed the outstanding bill balance.');
            }

            $payment = CustomerPayment::query()->create([
                'sale_id' => $sale->id,
                'amount' => $normalizedAmount,
                'paid_at' => $paidAt,
                'reference' => $reference,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            $remaining = round((float) $sale->outstanding_balance - $normalizedAmount, 2);

            $sale->update([
                'outstanding_balance' => $remaining,
            ]);

            $this->postsCustomerPaymentToLedger->handle($payment, $userId);

            return $payment->fresh(['journalEntry', 'sale']);
        });
    }
}
