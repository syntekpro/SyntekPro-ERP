<?php

namespace App\Livewire\Products;

use App\Models\Product;
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
        $this->authorize('viewAny', Product::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActive(int $productId, bool $isActive): void
    {
        $product = Product::query()->findOrFail($productId);

        $this->authorize('update', $product);

        $product->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Product activated.' : 'Product deactivated.');
        $this->resetPage();
    }

    public function delete(int $productId): void
    {
        $this->setActive($productId, false);
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('barcode', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.products.index-page', compact('products'));
    }
}