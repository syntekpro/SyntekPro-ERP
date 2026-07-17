<?php

namespace App\Livewire\Accounts;

use App\Models\Account;
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
        $this->authorize('viewAny', Account::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActive(int $accountId, bool $isActive): void
    {
        $account = Account::query()->findOrFail($accountId);

        $this->authorize('update', $account);

        $account->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Account activated.' : 'Account deactivated.');
        $this->resetPage();
    }

    public function delete(int $accountId): void
    {
        $this->setActive($accountId, false);
    }

    public function render()
    {
        $accounts = Account::query()
            ->with('parent')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('code')
            ->paginate(15);

        return view('livewire.accounts.index-page', compact('accounts'));
    }
}
