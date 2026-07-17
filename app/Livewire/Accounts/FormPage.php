<?php

namespace App\Livewire\Accounts;

use App\Enums\AccountType;
use App\Models\Account;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?Account $account = null;

    public string $code = '';

    public string $name = '';

    public string $account_type = 'asset';

    public ?int $parent_id = null;

    public bool $is_active = true;

    public function mount(?Account $account = null): void
    {
        $this->account = $account?->exists ? $account : null;

        if ($this->account) {
            $this->authorize('update', $this->account);

            $this->code = $this->account->code;
            $this->name = $this->account->name;
            $this->account_type = $this->account->account_type->value;
            $this->parent_id = $this->account->parent_id;
            $this->is_active = $this->account->is_active;

            return;
        }

        $this->authorize('create', Account::class);
    }

    public function save()
    {
        $validated = $this->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                Rule::unique('accounts', 'code')->ignore($this->account?->id),
            ],
            'name' => ['required', 'string', 'max:255'],
            'account_type' => ['required', Rule::in(array_map(fn (AccountType $type) => $type->value, AccountType::cases()))],
            'parent_id' => ['nullable', 'integer', Rule::exists('accounts', 'id')],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($this->account && $validated['parent_id'] === $this->account->id) {
            $this->addError('parent_id', 'An account cannot be its own parent.');

            return null;
        }

        if ($this->account) {
            $this->account->update($validated);
            session()->flash('status', 'Account updated.');
        } else {
            Account::query()->create($validated);
            session()->flash('status', 'Account created.');
        }

        return redirect()->route('accounts.index');
    }

    public function getParentOptionsProperty()
    {
        return Account::query()
            ->when($this->account?->id !== null, fn ($query) => $query->where('id', '!=', $this->account->id))
            ->orderBy('code')
            ->get();
    }

    public function getTypeOptionsProperty(): array
    {
        return AccountType::cases();
    }

    public function render()
    {
        return view('livewire.accounts.form-page');
    }
}
