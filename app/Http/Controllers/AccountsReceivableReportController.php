<?php

namespace App\Http\Controllers;

use App\Enums\SalePaymentMethod;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AccountsReceivableReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user !== null && ($user->isSuperAdmin() || $user->isAccountant()), 403);

        $today = Carbon::today();

        $openSales = Sale::query()
            ->with('customer')
            ->where('payment_method', SalePaymentMethod::CreditAccount->value)
            ->where('outstanding_balance', '>', 0)
            ->orderBy('due_date')
            ->get();

        $bucketsByCustomer = [];

        foreach ($openSales as $sale) {
            $customerId = $sale->customer_id;
            $customerName = $sale->customer?->name ?? 'Unknown customer';
            $daysOverdue = $sale->due_date?->lt($today) ? $sale->due_date->diffInDays($today) : 0;

            $bucket = match (true) {
                $daysOverdue <= 0 => 'current',
                $daysOverdue <= 30 => '1_30',
                $daysOverdue <= 60 => '31_60',
                $daysOverdue <= 90 => '61_90',
                default => '90_plus',
            };

            if (! isset($bucketsByCustomer[$customerId])) {
                $bucketsByCustomer[$customerId] = [
                    'customer_id' => $customerId,
                    'customer_name' => $customerName,
                    'current' => 0.0,
                    '1_30' => 0.0,
                    '31_60' => 0.0,
                    '61_90' => 0.0,
                    '90_plus' => 0.0,
                    'total' => 0.0,
                ];
            }

            $bucketsByCustomer[$customerId][$bucket] += (float) $sale->outstanding_balance;
            $bucketsByCustomer[$customerId]['total'] += (float) $sale->outstanding_balance;
        }

        return view('reports.ar-aging', [
            'rows' => collect($bucketsByCustomer)->sortBy('customer_name')->values(),
            'today' => $today,
        ]);
    }
}
