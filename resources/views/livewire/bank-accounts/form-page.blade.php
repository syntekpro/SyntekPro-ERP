<section class="mx-auto max-w-2xl space-y-6">
    <div class="flex items-start gap-3">
        <x-icon-tile color="ledger" size="lg">
            <x-lucide-landmark class="h-7 w-7" />
        </x-icon-tile>
        <div>
            <p class="text-xs font-medium text-ledger">Back office module</p>
            <h1 class="mt-1 text-3xl font-semibold text-ink">{{ $bankAccount ? 'Edit Bank Account' : 'Add Bank Account' }}</h1>
            <p class="mt-2 max-w-2xl text-sm text-muted">Link this bank account to a GL asset account so reconciliation can compare statement lines against journal entries.</p>
        </div>
    </div>

    <x-card surface="surface">
        <form wire:submit="save" class="space-y-5">
            <div>
                <label class="mb-2 block text-sm font-medium text-muted">GL Account <span class="text-danger">*</span></label>
                <x-select wire:model="account_id">
                    <option value="">Select an asset account</option>
                    @foreach ($assetAccounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} &mdash; {{ $account->name }}</option>
                    @endforeach
                </x-select>
                @error('account_id') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                @if ($assetAccounts->isEmpty())
                    <p class="mt-1 text-xs text-warning">No asset accounts exist yet. Create one under Accounting &rarr; Accounts first.</p>
                @endif
            </div>

            <div>
                <label class="mb-2 block text-sm font-medium text-muted">Bank Name <span class="text-danger">*</span></label>
                <x-input wire:model="bank_name" placeholder="e.g. Al Rajhi Bank" />
                @error('bank_name') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Account Holder Name</label>
                    <x-input wire:model="account_holder_name" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Account Number (last 4)</label>
                    <x-input wire:model="account_number_last4" maxlength="10" placeholder="****4821" />
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">IBAN</label>
                    <x-input wire:model="iban" placeholder="SA00 0000 0000 0000 0000 0000" />
                    @error('iban') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Currency</label>
                    <x-input wire:model="currency_code" maxlength="3" class="uppercase" />
                    @error('currency_code') <p class="mt-1 text-xs text-danger">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Opening Balance</label>
                    <x-input type="number" step="0.01" wire:model="opening_balance" />
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-muted">Opening Balance Date</label>
                    <x-input type="date" wire:model="opening_balance_date" />
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-ink">
                <input type="checkbox" wire:model="is_active" class="rounded border-line" />
                Active
            </label>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary">{{ $bankAccount ? 'Save changes' : 'Create bank account' }}</button>
                <a href="{{ route('bank-accounts.index') }}" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </x-card>
</section>
