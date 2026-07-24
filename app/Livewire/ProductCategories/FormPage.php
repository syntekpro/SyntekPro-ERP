<?php

namespace App\Livewire\ProductCategories;

use App\Models\ProductCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    public ?ProductCategory $productCategory = null;

    public string $name = '';

    public bool $is_active = true;

    public function mount(?ProductCategory $productCategory = null): void
    {
        abort_unless($this->canManageSettings(), 403);

        $this->productCategory = $productCategory?->exists ? $productCategory : null;

        if ($this->productCategory) {
            $this->name = $this->productCategory->name;
            $this->is_active = $this->productCategory->is_active;
        }
    }

    public function save()
    {
        abort_unless($this->canManageSettings(), 403);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('product_categories', 'name')->ignore($this->productCategory?->id)],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($this->productCategory) {
            $this->productCategory->update($validated);
            session()->flash('status', 'Product category updated.');
        } else {
            ProductCategory::query()->create($validated);
            session()->flash('status', 'Product category created.');
        }

        return redirect()->route('product-categories.index');
    }

    public function render()
    {
        return view('livewire.product-categories.form-page');
    }

    protected function canManageSettings(): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return (bool) $user?->hasPermission('settings.manage');
    }
}
