<?php

namespace App\Livewire\PriceCategories;

use App\Models\PriceCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    public ?PriceCategory $priceCategory = null;

    public string $name = '';

    public bool $is_active = true;

    public function mount(?PriceCategory $priceCategory = null): void
    {
        abort_unless($this->canManageSettings(), 403);

        $this->priceCategory = $priceCategory?->exists ? $priceCategory : null;

        if ($this->priceCategory) {
            $this->name = $this->priceCategory->name;
            $this->is_active = $this->priceCategory->is_active;
        }
    }

    public function save()
    {
        abort_unless($this->canManageSettings(), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('price_categories', 'name')->ignore($this->priceCategory?->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($this->priceCategory) {
            $this->priceCategory->update($validated);
            session()->flash('status', 'Price category updated.');
        } else {
            PriceCategory::query()->create($validated);
            session()->flash('status', 'Price category created.');
        }

        return redirect()->route('price-categories.index');
    }

    public function render()
    {
        return view('livewire.price-categories.form-page');
    }

    protected function canManageSettings(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('settings.manage');
    }
}