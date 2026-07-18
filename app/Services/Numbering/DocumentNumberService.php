<?php

namespace App\Services\Numbering;

use App\Models\DocumentCounter;

class DocumentNumberService
{
    public function next(string $key, string $prefix, int $pad = 6): string
    {
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
