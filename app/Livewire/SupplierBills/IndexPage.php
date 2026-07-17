<?php

namespace App\Livewire\SupplierBills;

use App\Models\SupplierBill;
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
        $this->authorize('viewAny', SupplierBill::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $bills = SupplierBill::query()
            ->with(['supplier', 'purchaseOrder'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('bill_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->latest('bill_date')
            ->latest('id')
            ->paginate(15);

        return view('livewire.supplier-bills.index-page', compact('bills'));
    }
}
