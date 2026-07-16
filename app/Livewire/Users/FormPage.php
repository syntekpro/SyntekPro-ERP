<?php

namespace App\Livewire\Users;

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?User $user = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'cashier';

    public ?int $shop_id = null;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(?User $user = null): void
    {
        $this->user = $user?->exists ? $user : null;

        if ($this->user) {
            $this->authorize('update', $this->user);

            $this->name = $this->user->name;
            $this->email = $this->user->email;
            $this->role = $this->user->role->value;
            $this->shop_id = $this->user->shop_id;

            return;
        }

        $this->authorize('create', User::class);
    }

    public function save()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user?->id)],
            'role' => ['required', Rule::in(array_map(fn (UserRole $role) => $role->value, UserRole::cases()))],
            'shop_id' => [
                Rule::requiredIf(fn (): bool => $this->role !== UserRole::SuperAdmin->value),
                'nullable',
                'integer',
                Rule::exists('shops', 'id'),
            ],
        ];

        if ($this->user) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'same:password_confirmation'];
        } else {
            $rules['password'] = ['required', 'string', 'min:8', 'same:password_confirmation'];
        }

        $validated = $this->validate($rules);

        if ($validated['role'] === UserRole::SuperAdmin->value) {
            $validated['shop_id'] = null;
        }

        $payload = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'shop_id' => $validated['shop_id'],
        ];

        if ($validated['password'] !== '') {
            $payload['password'] = $validated['password'];
        }

        if ($this->user) {
            $this->user->update($payload);
            session()->flash('status', 'User updated.');
        } else {
            User::query()->create($payload + [
                'email_verified_at' => now(),
            ]);
            session()->flash('status', 'User created.');
        }

        return redirect()->route('users.index');
    }

    public function getRoleOptionsProperty(): array
    {
        return UserRole::cases();
    }

    public function getShopOptionsProperty()
    {
        return Shop::query()->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.users.form-page');
    }
}