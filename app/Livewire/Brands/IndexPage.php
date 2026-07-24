<?php

namespace App\Livewire\Brands;

use App\Models\Brand;
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

    public function setActive(int $brandId, bool $isActive): void
    {
        abort_unless($this->canManageSettings(), 403);

        $brand = Brand::query()->findOrFail($brandId);
        $brand->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Brand activated.' : 'Brand deactivated.');
        $this->resetPage();
    }

    public function render()
    {
        $brands = Brand::query()
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.brands.index-page', compact('brands'));
    }

    protected function canManageSettings(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('settings.manage');
    }
}
