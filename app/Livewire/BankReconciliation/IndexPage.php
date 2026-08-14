<?php

namespace App\Livewire\BankReconciliation;

use App\Models\BankAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class IndexPage extends Component
{
    use AuthorizesRequests;

    public function mount(): void
    {
        $this->authorize('viewAny', BankAccount::class);
    }

    public function render()
    {
        $bankAccounts = BankAccount::query()
            ->with('account')
            ->withCount([
                'statementLines as unmatched_count' => function ($query): void {
                    $query->where('match_status', 'unmatched');
                },
            ])
            ->where('is_active', true)
            ->orderBy('bank_name')
            ->get();

        return view('livewire.bank-reconciliation.index-page', compact('bankAccounts'));
    }
}
