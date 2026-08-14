<?php

namespace App\Livewire\BankAccounts;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\BankAccount;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?BankAccount $bankAccount = null;

    public ?int $account_id = null;

    public string $bank_name = '';

    public string $account_holder_name = '';

    public string $account_number_last4 = '';

    public string $iban = '';

    public string $currency_code = 'SAR';

    public ?float $opening_balance = 0;

    public ?string $opening_balance_date = null;

    public bool $is_active = true;

    public function mount(?BankAccount $bankAccount = null): void
    {
        $this->bankAccount = $bankAccount?->exists ? $bankAccount : null;

        if ($this->bankAccount) {
            $this->authorize('update', $this->bankAccount);

            $this->account_id = $this->bankAccount->account_id;
            $this->bank_name = $this->bankAccount->bank_name;
            $this->account_holder_name = (string) ($this->bankAccount->account_holder_name ?? '');
            $this->account_number_last4 = (string) ($this->bankAccount->account_number_last4 ?? '');
            $this->iban = (string) ($this->bankAccount->iban ?? '');
            $this->currency_code = $this->bankAccount->currency_code;
            $this->opening_balance = (float) $this->bankAccount->opening_balance;
            $this->opening_balance_date = $this->bankAccount->opening_balance_date?->format('Y-m-d');
            $this->is_active = $this->bankAccount->is_active;

            return;
        }

        $this->authorize('create', BankAccount::class);
    }

    public function save()
    {
        $validated = $this->validate([
            'account_id' => ['required', 'exists:accounts,id'],
            'bank_name' => ['required', 'string', 'max:255'],
            'account_holder_name' => ['nullable', 'string', 'max:255'],
            'account_number_last4' => ['nullable', 'string', 'max:10'],
            'iban' => ['nullable', 'string', 'max:34'],
            'currency_code' => ['required', 'string', 'size:3'],
            'opening_balance' => ['nullable', 'numeric'],
            'opening_balance_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ]);

        if ($this->bankAccount) {
            $this->bankAccount->update($validated);
            session()->flash('status', 'Bank account updated.');
        } else {
            BankAccount::create($validated);
            session()->flash('status', 'Bank account created.');
        }

        return redirect()->route('bank-accounts.index');
    }

    public function render()
    {
        $assetAccounts = Account::query()
            ->where('account_type', AccountType::Asset)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('livewire.bank-accounts.form-page', compact('assetAccounts'));
    }
}
