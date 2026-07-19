<?php

namespace App\Livewire\Products;

use App\Models\PriceCategory;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public string $unitFilter = '';

    public string $priceCategoryFilter = '';

    public string $vatRateFilter = '';

    public string $sortField = 'name';

    public string $sortDirection = 'asc';

    public array $selectedProductIds = [];

    protected array $sortableFields = [
        'sku' => 'sku',
        'name' => 'name',
        'price' => 'price',
        'cost_price' => 'cost_price',
        'base_unit_id' => 'base_unit_id',
    ];

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatedUnitFilter(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatedPriceCategoryFilter(): void
    {
        $this->resetPageAndSelection();
    }

    public function updatedVatRateFilter(): void
    {
        $this->resetPageAndSelection();
    }

    public function sortBy(string $field): void
    {
        if (! array_key_exists($field, $this->sortableFields)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'statusFilter', 'unitFilter', 'priceCategoryFilter', 'vatRateFilter', 'selectedProductIds']);
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

    public function bulkSetActive(bool $isActive): void
    {
        $productIds = collect($this->selectedProductIds)->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($productIds->isEmpty()) {
            return;
        }

        Product::query()
            ->whereKey($productIds)
            ->get()
            ->each(function (Product $product) use ($isActive): void {
                $this->authorize('update', $product);
                $product->update(['is_active' => $isActive]);
            });

        $this->selectedProductIds = [];
        session()->flash('status', $isActive ? 'Selected products activated.' : 'Selected products deactivated.');
        $this->resetPage();
    }

    protected function resetPageAndSelection(): void
    {
        $this->selectedProductIds = [];
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->with(['baseUnit', 'prices.priceCategory', 'unitConversions.unit'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('sku', 'like', '%'.$this->search.'%')
                        ->orWhere('barcode', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($query): void {
                $query->where('is_active', $this->statusFilter === 'active');
            })
            ->when($this->unitFilter !== '', function ($query): void {
                $query->where('base_unit_id', (int) $this->unitFilter);
            })
            ->when($this->priceCategoryFilter !== '', function ($query): void {
                $query->whereHas('prices', fn ($priceQuery) => $priceQuery->where('price_category_id', (int) $this->priceCategoryFilter));
            })
            ->when($this->vatRateFilter !== '', function ($query): void {
                $query->where('vat_rate', (float) $this->vatRateFilter);
            })
            ->orderBy($this->sortableFields[$this->sortField] ?? 'name', $this->sortDirection === 'desc' ? 'desc' : 'asc')
            ->paginate(10);

        return view('livewire.products.index-page', [
            'products' => $products,
            'unitOptions' => Unit::query()->where('is_active', true)->orderBy('code')->get(),
            'priceCategoryOptions' => PriceCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'vatRateOptions' => Product::query()->select('vat_rate')->distinct()->orderBy('vat_rate')->pluck('vat_rate'),
        ]);
    }
}