<?php

namespace App\Livewire\Brands;

use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    public ?Brand $brand = null;

    public string $name = '';

    public bool $is_active = true;

    public function mount(?Brand $brand = null): void
    {
        abort_unless($this->canManageSettings(), 403);

        $this->brand = $brand?->exists ? $brand : null;

        if ($this->brand) {
            $this->name = $this->brand->name;
            $this->is_active = $this->brand->is_active;
        }
    }

    public function save()
    {
        abort_unless($this->canManageSettings(), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($this->brand?->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($this->brand) {
            $this->brand->update($validated);
            session()->flash('status', 'Brand updated.');
        } else {
            Brand::query()->create($validated);
            session()->flash('status', 'Brand created.');
        }

        return redirect()->route('brands.index');
    }

    public function render()
    {
        return view('livewire.brands.form-page');
    }

    protected function canManageSettings(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('settings.manage');
    }
}
