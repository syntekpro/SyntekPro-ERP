<?php

namespace App\Livewire\Shops;

use App\Models\PriceCategory;
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

    public string $legal_name = '';

    public string $vat_registration_number = '';

    public ?int $default_price_category_id = null;

    public string $slug = '';

    public bool $is_active = true;

    protected bool $slugWasManuallyEdited = false;

    public function mount(?Shop $shop = null): void
    {
        $this->shop = $shop?->exists ? $shop : null;

        if ($this->shop) {
            $this->authorize('update', $this->shop);

            $this->name = $this->shop->name;
            $this->legal_name = (string) ($this->shop->legal_name ?? '');
            $this->vat_registration_number = (string) ($this->shop->vat_registration_number ?? '');
            $this->default_price_category_id = $this->shop->default_price_category_id;
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
            'legal_name' => ['nullable', 'string', 'max:255'],
            'vat_registration_number' => ['nullable', 'string', 'max:32'],
            'default_price_category_id' => ['nullable', 'integer', Rule::exists('price_categories', 'id')],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shops', 'slug')->ignore($this->shop?->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['legal_name'] = $validated['legal_name'] === '' ? null : $validated['legal_name'];
        $validated['vat_registration_number'] = $validated['vat_registration_number'] === '' ? null : $validated['vat_registration_number'];
        $validated['default_price_category_id'] = $validated['default_price_category_id'] ?: null;

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

    public function getPriceCategoryOptionsProperty()
    {
        return PriceCategory::query()->where('is_active', true)->orderBy('name')->get();
    }
}