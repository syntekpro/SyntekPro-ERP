<?php

namespace App\Livewire\Users;

use App\Models\User;
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
        $this->authorize('viewAny', User::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setActive(int $userId, bool $isActive): void
    {
        $user = User::query()->findOrFail($userId);

        $this->authorize('update', $user);

        $user->update(['is_active' => $isActive]);

        session()->flash('status', $isActive ? 'User activated.' : 'User deactivated.');
        $this->resetPage();
    }

    public function delete(int $userId): void
    {
        $this->setActive($userId, false);
    }

    public function render()
    {
        $users = User::query()
            ->with('shop')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.users.index-page', compact('users'));
    }
}