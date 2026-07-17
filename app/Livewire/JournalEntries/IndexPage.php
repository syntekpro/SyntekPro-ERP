<?php

namespace App\Livewire\JournalEntries;

use App\Models\JournalEntry;
use App\Models\Shop;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public ?int $shop_id = null;

    public string $start_date = '';

    public string $end_date = '';

    public bool $isShopScopedUser = false;

    public function mount(): void
    {
        $this->authorize('viewAny', JournalEntry::class);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        $this->isShopScopedUser = (bool) $user?->isShopManager();

        if ($this->isShopScopedUser) {
            $this->shop_id = $user?->shop_id;
        }
    }

    public function updatingShopId(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function getShopOptionsProperty()
    {
        return Shop::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        $query = JournalEntry::query()
            ->with(['shop', 'creator'])
            ->when($this->shop_id !== null, fn ($inner) => $inner->where('shop_id', $this->shop_id))
            ->when($this->start_date !== '', fn ($inner) => $inner->whereDate('entry_date', '>=', $this->start_date))
            ->when($this->end_date !== '', fn ($inner) => $inner->whereDate('entry_date', '<=', $this->end_date))
            ->latest('entry_date')
            ->latest('id');

        if ($this->isShopScopedUser) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            $query->where('shop_id', $user?->shop_id);
        }

        $entries = $query->paginate(15);

        return view('livewire.journal-entries.index-page', [
            'entries' => $entries,
        ]);
    }
}
