<?php

namespace App\Livewire\Shops;

use App\Models\Shop;
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
        $this->authorize('viewAny', Shop::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function delete(int $shopId): void
    {
        $shop = Shop::query()->findOrFail($shopId);

        $this->authorize('delete', $shop);

        $shop->delete();

        session()->flash('status', 'Shop deleted.');
        $this->resetPage();
    }

    public function render()
    {
        $shops = Shop::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('slug', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.shops.index-page', compact('shops'));
    }
}