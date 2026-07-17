<?php

namespace App\Services\Purchasing;

use App\Enums\SupplierBillStatus;
use App\Models\SupplierPayment;
use App\Services\Accounting\PostsSupplierPaymentToLedger;
use Illuminate\Support\Facades\DB;

class SupplierPaymentService
{
    public function __construct(protected PostsSupplierPaymentToLedger $postsSupplierPaymentToLedger)
    {
    }

    public function record(int $billId, float $amount, string $paidAt, ?string $reference, ?string $notes, ?int $userId = null): SupplierPayment
    {
        return DB::transaction(function () use ($billId, $amount, $paidAt, $reference, $notes, $userId): SupplierPayment {
            $bill = \App\Models\SupplierBill::query()
                ->whereKey($billId)
                ->lockForUpdate()
                ->firstOrFail();

            $normalizedAmount = round($amount, 2);

            if ($normalizedAmount <= 0) {
                throw new \RuntimeException('Payment amount must be greater than zero.');
            }

            if ($normalizedAmount > (float) $bill->outstanding_balance) {
                throw new \RuntimeException('Payment amount cannot exceed the outstanding bill balance.');
            }

            $payment = SupplierPayment::query()->create([
                'supplier_bill_id' => $bill->id,
                'amount' => $normalizedAmount,
                'paid_at' => $paidAt,
                'reference' => $reference,
                'notes' => $notes,
                'created_by' => $userId,
            ]);

            $remaining = round((float) $bill->outstanding_balance - $normalizedAmount, 2);

            $bill->update([
                'outstanding_balance' => $remaining,
                'status' => $remaining <= 0 ? SupplierBillStatus::Paid : SupplierBillStatus::PartiallyPaid,
            ]);

            $this->postsSupplierPaymentToLedger->handle($payment, $userId);

            return $payment->fresh(['journalEntry', 'bill']);
        });
    }
}
