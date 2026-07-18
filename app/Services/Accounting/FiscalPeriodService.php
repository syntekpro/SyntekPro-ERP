<?php

namespace App\Services\Accounting;

use App\Models\FiscalPeriod;
use Carbon\CarbonImmutable;

class FiscalPeriodService
{
    public function ensureYear(int $year): void
    {
        for ($month = 1; $month <= 12; $month++) {
            $start = CarbonImmutable::create($year, $month, 1)->startOfDay();

            FiscalPeriod::query()->firstOrCreate([
                'year' => $year,
                'month' => $month,
            ], [
                'period_start' => $start->toDateString(),
                'period_end' => $start->endOfMonth()->toDateString(),
                'is_closed' => false,
            ]);
        }
    }
}
