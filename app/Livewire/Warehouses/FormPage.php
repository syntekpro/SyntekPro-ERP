<?php

namespace App\Livewire\Warehouses;

use App\Models\Warehouse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?Warehouse $warehouse = null;

    public string $name = '';

    public string $code = '';

    public bool $is_active = true;

    protected bool $codeWasManuallyEdited = false;

    public function mount(?Warehouse $warehouse = null): void
    {
        $this->warehouse = $warehouse?->exists ? $warehouse : null;

        if ($this->warehouse) {
            $this->authorize('update', $this->warehouse);

            $this->name = $this->warehouse->name;
            $this->code = $this->warehouse->code;
            $this->is_active = $this->warehouse->is_active;
            $this->codeWasManuallyEdited = true;

            return;
        }

        $this->authorize('create', Warehouse::class);
    }

    public function updatedName(string $value): void
    {
        if (! $this->codeWasManuallyEdited) {
            $this->code = Str::upper(Str::slug($value, '-'));
        }
    }

    public function updatedCode(): void
    {
        $this->codeWasManuallyEdited = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')->ignore($this->warehouse?->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($this->warehouse) {
            $this->warehouse->update($validated);
            session()->flash('status', 'Warehouse updated.');
        } else {
            Warehouse::query()->create($validated);
            session()->flash('status', 'Warehouse created.');
        }

        return redirect()->route('warehouses.index');
    }

    public function render()
    {
        return view('livewire.warehouses.form-page');
    }
}