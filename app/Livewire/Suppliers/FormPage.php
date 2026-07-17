<?php

namespace App\Livewire\Suppliers;

use App\Models\Supplier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?Supplier $supplier = null;

    public string $name = '';

    public string $code = '';

    public string $contact_name = '';

    public string $phone = '';

    public string $email = '';

    public string $vat_registration_number = '';

    public int $payment_terms_days = 30;

    public bool $is_active = true;

    public function mount(?Supplier $supplier = null): void
    {
        $this->supplier = $supplier?->exists ? $supplier : null;

        if ($this->supplier) {
            $this->authorize('update', $this->supplier);

            $this->name = $this->supplier->name;
            $this->code = $this->supplier->code;
            $this->contact_name = (string) ($this->supplier->contact_name ?? '');
            $this->phone = (string) ($this->supplier->phone ?? '');
            $this->email = (string) ($this->supplier->email ?? '');
            $this->vat_registration_number = (string) ($this->supplier->vat_registration_number ?? '');
            $this->payment_terms_days = (int) $this->supplier->payment_terms_days;
            $this->is_active = $this->supplier->is_active;

            return;
        }

        $this->authorize('create', Supplier::class);
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:40', Rule::unique('suppliers', 'code')->ignore($this->supplier?->id)],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:255'],
            'vat_registration_number' => ['nullable', 'string', 'max:32'],
            'payment_terms_days' => ['required', 'integer', 'min:0', 'max:365'],
            'is_active' => ['required', 'boolean'],
        ]);

        foreach (['contact_name', 'phone', 'email', 'vat_registration_number'] as $nullableField) {
            if (($validated[$nullableField] ?? '') === '') {
                $validated[$nullableField] = null;
            }
        }

        if ($this->supplier) {
            $this->supplier->update($validated);
            session()->flash('status', 'Supplier updated.');
        } else {
            Supplier::query()->create($validated);
            session()->flash('status', 'Supplier created.');
        }

        return redirect()->route('suppliers.index');
    }

    public function render()
    {
        return view('livewire.suppliers.form-page');
    }
}
