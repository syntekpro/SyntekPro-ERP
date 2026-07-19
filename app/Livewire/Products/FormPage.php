<?php

namespace App\Livewire\Products;

use App\Models\PriceCategory;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?Product $product = null;

    public string $name = '';

    public string $sku = '';

    public string $barcode = '';

    public ?int $base_unit_id = null;

    public string $price = '0.00';

    public string $cost_price = '0.00';

    public string $vat_rate = '15.00';

    public bool $is_excise_applicable = false;

    public string $excise_rate = '';

    public bool $is_active = true;

    public array $unit_conversions = [];

    public array $category_prices = [];

    protected bool $skuWasManuallyEdited = false;

    public function mount(?Product $product = null): void
    {
        $this->product = $product?->exists ? $product->load(['unitConversions', 'prices']) : null;

        if ($this->product) {
            $this->authorize('update', $this->product);

            $this->name = $this->product->name;
            $this->sku = $this->product->sku;
            $this->barcode = (string) ($this->product->barcode ?? '');
            $this->base_unit_id = $this->product->base_unit_id;
            $this->price = number_format((float) $this->product->price, 2, '.', '');
            $this->cost_price = number_format((float) $this->product->cost_price, 2, '.', '');
            $this->vat_rate = number_format((float) $this->product->vat_rate, 2, '.', '');
            $this->is_excise_applicable = $this->product->is_excise_applicable;
            $this->excise_rate = $this->product->excise_rate !== null ? number_format((float) $this->product->excise_rate, 2, '.', '') : '';
            $this->is_active = $this->product->is_active;
            $this->unit_conversions = $this->product->unitConversions->map(fn ($conversion) => [
                'unit_id' => $conversion->unit_id,
                'conversion_factor' => number_format((float) $conversion->conversion_factor, 6, '.', ''),
            ])->values()->all();
            $this->category_prices = $this->product->prices
                ->mapWithKeys(fn ($price) => [$price->price_category_id => number_format((float) $price->price, 2, '.', '')])
                ->all();
            $this->skuWasManuallyEdited = true;

            return;
        }

        $this->authorize('create', Product::class);
        $this->base_unit_id = Unit::query()->where('code', 'PCS')->value('id');
    }

    public function updatedName(string $value): void
    {
        if (! $this->skuWasManuallyEdited) {
            $this->sku = Str::upper(Str::slug($value, '-'));
        }
    }

    public function updatedSku(): void
    {
        $this->skuWasManuallyEdited = true;
    }

    public function addUnitConversion(): void
    {
        $this->unit_conversions[] = ['unit_id' => null, 'conversion_factor' => '1.000000'];
    }

    public function removeUnitConversion(int $index): void
    {
        unset($this->unit_conversions[$index]);
        $this->unit_conversions = array_values($this->unit_conversions);
    }

    public function save()
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:64',
                Rule::unique('products', 'sku')->ignore($this->product?->id),
            ],
            'barcode' => [
                'nullable',
                'string',
                'max:64',
                Rule::unique('products', 'barcode')->ignore($this->product?->id),
            ],
            'base_unit_id' => ['required', 'integer', Rule::exists('units', 'id')],
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['required', 'numeric', 'min:0'],
            'is_excise_applicable' => ['required', 'boolean'],
            'excise_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['required', 'boolean'],
            'unit_conversions' => ['array'],
            'unit_conversions.*.unit_id' => ['nullable', 'integer', Rule::exists('units', 'id')],
            'unit_conversions.*.conversion_factor' => ['nullable', 'numeric', 'gt:0'],
            'category_prices' => ['array'],
            'category_prices.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $validated['barcode'] = $validated['barcode'] === '' ? null : $validated['barcode'];
        $validated['excise_rate'] = $validated['is_excise_applicable'] ? ($validated['excise_rate'] === '' ? null : $validated['excise_rate']) : null;

        $productPayload = collect($validated)->except(['unit_conversions', 'category_prices'])->all();

        if ($this->product) {
            $this->product->update($productPayload);
            $product = $this->product;
            session()->flash('status', 'Product updated.');
        } else {
            $product = Product::query()->create($productPayload);
            session()->flash('status', 'Product created.');
        }

        $this->syncUnitConversions($product, $validated['unit_conversions'] ?? []);
        $this->syncCategoryPrices($product, $validated['category_prices'] ?? []);

        return redirect()->route('products.index');
    }

    protected function syncUnitConversions(Product $product, array $conversions): void
    {
        $product->unitConversions()->delete();

        foreach ($conversions as $conversion) {
            if (empty($conversion['unit_id']) || (int) $conversion['unit_id'] === (int) $product->base_unit_id) {
                continue;
            }

            $product->unitConversions()->updateOrCreate([
                'unit_id' => (int) $conversion['unit_id'],
            ], [
                'conversion_factor' => $conversion['conversion_factor'],
            ]);
        }
    }

    protected function syncCategoryPrices(Product $product, array $prices): void
    {
        foreach ($this->priceCategoryOptions as $category) {
            $price = $prices[$category->id] ?? null;

            if ($price === null || $price === '') {
                $product->prices()->where('price_category_id', $category->id)->delete();
                continue;
            }

            $product->prices()->updateOrCreate([
                'price_category_id' => $category->id,
            ], [
                'price' => $price,
            ]);
        }
    }

    public function getUnitOptionsProperty()
    {
        return Unit::query()->where('is_active', true)->orderBy('code')->get();
    }

    public function getPriceCategoryOptionsProperty()
    {
        return PriceCategory::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.products.form-page');
    }
}