<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
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
        $this->authorize('viewAny', Customer::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActive(int $customerId, bool $isActive): void
    {
        $customer = Customer::query()->findOrFail($customerId);

        $this->authorize('update', $customer);

        $customer->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Customer activated.' : 'Customer deactivated.');
    }

    public function delete(int $customerId): void
    {
        $this->setActive($customerId, false);
    }

    public function render()
    {
        $customers = Customer::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.customers.index-page', compact('customers'));
    }
}
