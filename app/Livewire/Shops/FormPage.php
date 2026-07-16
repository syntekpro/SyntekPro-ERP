<?php

namespace App\Livewire\Shops;

use App\Models\Shop;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?Shop $shop = null;

    public string $name = '';

    public string $slug = '';

    public bool $is_active = true;

    protected bool $slugWasManuallyEdited = false;

    public function mount(?Shop $shop = null): void
    {
        $this->shop = $shop?->exists ? $shop : null;

        if ($this->shop) {
            $this->authorize('update', $this->shop);

            $this->name = $this->shop->name;
            $this->slug = $this->shop->slug;
            $this->is_active = $this->shop->is_active;
            $this->slugWasManuallyEdited = true;

            return;
        }

        $this->authorize('create', Shop::class);
    }

    public function updatedName(string $value): void
    {
        if (! $this->slugWasManuallyEdited) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugWasManuallyEdited = true;
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shops', 'slug')->ignore($this->shop?->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($this->shop) {
            $this->shop->update($validated);
            session()->flash('status', 'Shop updated.');
        } else {
            Shop::query()->create($validated);
            session()->flash('status', 'Shop created.');
        }

        return redirect()->route('shops.index');
    }

    public function render()
    {
        return view('livewire.shops.form-page');
    }
}