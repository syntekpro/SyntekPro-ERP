<?php

namespace App\Http\Controllers;

use App\Models\FiscalPeriod;
use App\Services\Accounting\FiscalPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FiscalPeriodController extends Controller
{
    public function __construct(protected FiscalPeriodService $fiscalPeriodService)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant()), 403);

        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2200'],
        ]);

        $year = (int) ($validated['year'] ?? now()->year);

        $this->fiscalPeriodService->ensureYear($year);

        return view('reports.fiscal-periods', [
            'year' => $year,
            'periods' => FiscalPeriod::query()->where('year', $year)->orderBy('month')->get(),
            'canManage' => $user->isSuperAdmin(),
        ]);
    }

    public function close(Request $request, FiscalPeriod $period): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null && $user->isSuperAdmin(), 403);

        DB::transaction(function () use ($period, $user): void {
            $period->update([
                'is_closed' => true,
                'closed_by' => $user->id,
                'closed_at' => now(),
            ]);
        });

        return back()->with('status', 'Fiscal period closed.');
    }

    public function reopen(Request $request, FiscalPeriod $period): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user !== null && $user->isSuperAdmin(), 403);

        DB::transaction(function () use ($period): void {
            $period->update([
                'is_closed' => false,
                'closed_by' => null,
                'closed_at' => null,
            ]);
        });

        return back()->with('status', 'Fiscal period reopened.');
    }
}
