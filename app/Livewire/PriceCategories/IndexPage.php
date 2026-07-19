<?php

namespace App\Livewire\PriceCategories;

use App\Models\PriceCategory;
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

    public function setActive(int $priceCategoryId, bool $isActive): void
    {
        abort_unless($this->canManageSettings(), 403);

        $priceCategory = PriceCategory::query()->findOrFail($priceCategoryId);
        $priceCategory->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Price category activated.' : 'Price category deactivated.');
        $this->resetPage();
    }

    public function render()
    {
        $priceCategories = PriceCategory::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.price-categories.index-page', compact('priceCategories'));
    }

    protected function canManageSettings(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('settings.manage');
    }
}