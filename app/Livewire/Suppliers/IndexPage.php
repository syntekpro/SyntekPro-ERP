<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
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
        $this->authorize('viewAny', Supplier::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActive(int $supplierId, bool $isActive): void
    {
        $supplier = Supplier::query()->findOrFail($supplierId);

        $this->authorize('update', $supplier);

        $supplier->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Supplier activated.' : 'Supplier deactivated.');
    }

    public function delete(int $supplierId): void
    {
        $this->setActive($supplierId, false);
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(12);

        return view('livewire.suppliers.index-page', compact('suppliers'));
    }
}
