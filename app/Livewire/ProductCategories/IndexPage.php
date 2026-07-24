<?php

namespace App\Livewire\ProductCategories;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        abort_unless($this->canManageSettings(), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActive(int $productCategoryId, bool $isActive): void
    {
        abort_unless($this->canManageSettings(), 403);

        $productCategory = ProductCategory::query()->findOrFail($productCategoryId);
        $productCategory->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Product category activated.' : 'Product category deactivated.');
        $this->resetPage();
    }

    public function render()
    {
        $productCategories = ProductCategory::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.product-categories.index-page', compact('productCategories'));
    }

    protected function canManageSettings(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('settings.manage');
    }
}
