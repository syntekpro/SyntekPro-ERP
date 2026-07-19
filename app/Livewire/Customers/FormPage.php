<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use App\Models\PriceCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?Customer $customer = null;

    public string $name = '';

    public string $code = '';

    public string $contact_name = '';

    public string $phone = '';

    public string $email = '';

    public string $vat_registration_number = '';

    public int $payment_terms_days = 30;

    public string $credit_limit = '';

    public ?int $default_price_category_id = null;

    public bool $is_active = true;

    public function mount(?Customer $customer = null): void
    {
        $this->customer = $customer?->exists ? $customer : null;

        if ($this->customer) {
            $this->authorize('update', $this->customer);

            $this->name = $this->customer->name;
            $this->code = $this->customer->code;
            $this->contact_name = (string) ($this->customer->contact_name ?? '');
            $this->phone = (string) ($this->customer->phone ?? '');
            $this->email = (string) ($this->customer->email ?? '');
            $this->vat_registration_number = (string) ($this->customer->vat_registration_number ?? '');
            $this->payment_terms_days = (int) $this->customer->payment_terms_days;
            $this->credit_limit = $this->customer->credit_limit !== null ? number_format((float) $this->customer->credit_limit, 2, '.', '') : '';
            $this->default_price_category_id = $this->customer->default_price_category_id;
            $this->is_active = $this->customer->is_active;

            return;
        }

        $this->authorize('create', Customer::class);
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40', Rule::unique('customers', 'code')->ignore($this->customer?->id)],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'vat_registration_number' => ['nullable', 'string', 'max:32'],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:365'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'default_price_category_id' => ['nullable', 'integer', Rule::exists('price_categories', 'id')],
            'is_active' => ['required', 'boolean'],
        ]);

        foreach (['contact_name', 'phone', 'email', 'vat_registration_number'] as $nullableField) {
            if (($validated[$nullableField] ?? '') === '') {
                $validated[$nullableField] = null;
            }
        }

        $validated['credit_limit'] = ($validated['credit_limit'] ?? '') === '' ? null : $validated['credit_limit'];
        $validated['default_price_category_id'] = $validated['default_price_category_id'] ?: null;

        if ($this->customer) {
            $this->customer->update($validated);
            session()->flash('status', 'Customer updated.');
        } else {
            Customer::query()->create($validated);
            session()->flash('status', 'Customer created.');
        }

        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customers.form-page');
    }

    public function getPriceCategoryOptionsProperty()
    {
        return PriceCategory::query()->where('is_active', true)->orderBy('name')->get();
    }
}
