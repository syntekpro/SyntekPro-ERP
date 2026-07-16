<?php

namespace App\Livewire\Products;

use App\Models\Product;
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

    public string $price = '0.00';

    public string $cost_price = '0.00';

    public string $vat_rate = '15.00';

    public bool $is_active = true;

    protected bool $skuWasManuallyEdited = false;

    public function mount(?Product $product = null): void
    {
        $this->product = $product?->exists ? $product : null;

        if ($this->product) {
            $this->authorize('update', $this->product);

            $this->name = $this->product->name;
            $this->sku = $this->product->sku;
            $this->barcode = (string) ($this->product->barcode ?? '');
            $this->price = number_format((float) $this->product->price, 2, '.', '');
            $this->cost_price = number_format((float) $this->product->cost_price, 2, '.', '');
            $this->vat_rate = number_format((float) $this->product->vat_rate, 2, '.', '');
            $this->is_active = $this->product->is_active;
            $this->skuWasManuallyEdited = true;

            return;
        }

        $this->authorize('create', Product::class);
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
            'price' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'vat_rate' => ['required', 'numeric', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['barcode'] = $validated['barcode'] === '' ? null : $validated['barcode'];

        if ($this->product) {
            $this->product->update($validated);
            session()->flash('status', 'Product updated.');
        } else {
            Product::query()->create($validated);
            session()->flash('status', 'Product created.');
        }

        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.products.form-page');
    }
}