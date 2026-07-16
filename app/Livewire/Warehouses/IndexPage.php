<?php

namespace App\Livewire\Warehouses;

use App\Models\Warehouse;
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
        $this->authorize('viewAny', Warehouse::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $warehouseId): void
    {
        $warehouse = Warehouse::query()->findOrFail($warehouseId);

        $this->authorize('delete', $warehouse);

        $warehouse->delete();

        session()->flash('status', 'Warehouse deleted.');
        $this->resetPage();
    }

    public function render()
    {
        $warehouses = Warehouse::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.warehouses.index-page', compact('warehouses'));
    }
}