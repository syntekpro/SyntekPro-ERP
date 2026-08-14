<?php

namespace App\Livewire\BankAccounts;

use App\Models\BankAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', BankAccount::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActive(int $bankAccountId, bool $isActive): void
    {
        $bankAccount = BankAccount::query()->findOrFail($bankAccountId);

        $this->authorize('update', $bankAccount);

        $bankAccount->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Bank account activated.' : 'Bank account deactivated.');
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
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('bank_name', 'like', '%'.$this->search.'%')
                        ->orWhere('iban', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('bank_name')
            ->paginate(10);

        return view('livewire.bank-accounts.index-page', compact('bankAccounts'));
    }
}
