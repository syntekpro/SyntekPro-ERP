<?php

namespace App\Livewire\Units;

use App\Models\Unit;
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

    public function setActive(int $unitId, bool $isActive): void
    {
        abort_unless($this->canManageSettings(), 403);

        $unit = Unit::query()->findOrFail($unitId);
        $unit->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'Unit activated.' : 'Unit deactivated.');
        $this->resetPage();
    }

    public function render()
    {
        $units = Unit::query()
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('code', 'like', '%'.$this->search.'%')
                        ->orWhere('name', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('code')
            ->paginate(10);

        return view('livewire.units.index-page', compact('units'));
    }

    protected function canManageSettings(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('settings.manage');
    }
}