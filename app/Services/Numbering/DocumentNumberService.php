<?php

namespace App\Services\Numbering;

use App\Models\DocumentCounter;
use App\Models\DocumentNumberFormat;

class DocumentNumberService
{
    protected const DEFAULT_PREFIXES = [
        'sales' => 'INV-',
        'credit_note' => 'CN-',
        'debit_note' => 'DN-',
        'purchase_orders' => 'PO-',
        'supplier_bills' => 'BILL-',
        'stock_transfers' => 'ST-',
    ];

    public function next(string $key, ?string $fallbackPrefix = null, int $pad = 6): string
    {
        $prefix = DocumentNumberFormat::query()->where('key', $key)->value('prefix')
            ?: $fallbackPrefix
            ?: self::DEFAULT_PREFIXES[$key]
            ?? strtoupper(str_replace('_', '-', $key)).'-';

        $counter = DocumentCounter::query()
            ->where('key', $key)
            ->lockForUpdate()
            ->first();

        if ($counter === null) {
            $counter = DocumentCounter::query()->create([
                'key' => $key,
                'next_number' => 2,
            ]);

            $number = 1;
        } else {
            $number = (int) $counter->next_number;

            $counter->update([
                'next_number' => $number + 1,
            ]);
        }

        return $prefix.str_pad((string) $number, $pad, '0', STR_PAD_LEFT);
    }
}
