<?php

namespace App\Http\Controllers;

use App\Models\SupplierBill;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AccountsPayableReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant()), 403);

        $today = Carbon::today();

        $openBills = SupplierBill::query()
            ->with('supplier')
            ->where('outstanding_balance', '>', 0)
            ->orderBy('due_date')
            ->get();

        $bucketsBySupplier = [];

        foreach ($openBills as $bill) {
            $supplierId = $bill->supplier_id;
            $supplierName = $bill->supplier?->name ?? 'Unknown supplier';
            $daysOverdue = $bill->due_date?->lt($today) ? $bill->due_date->diffInDays($today) : 0;

            $bucket = match (true) {
                $daysOverdue <= 0 => 'current',
                $daysOverdue <= 30 => '1_30',
                $daysOverdue <= 60 => '31_60',
                $daysOverdue <= 90 => '61_90',
                default => '90_plus',
            };

            if (! isset($bucketsBySupplier[$supplierId])) {
                $bucketsBySupplier[$supplierId] = [
                    'supplier_id' => $supplierId,
                    'supplier_name' => $supplierName,
                    'current' => 0.0,
                    '1_30' => 0.0,
                    '31_60' => 0.0,
                    '61_90' => 0.0,
                    '90_plus' => 0.0,
                    'total' => 0.0,
                ];
            }

            $bucketsBySupplier[$supplierId][$bucket] += (float) $bill->outstanding_balance;
            $bucketsBySupplier[$supplierId]['total'] += (float) $bill->outstanding_balance;
        }

        return view('reports.ap-aging', [
            'rows' => collect($bucketsBySupplier)->sortBy('supplier_name')->values(),
            'today' => $today,
        ]);
    }
}
