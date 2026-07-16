<?php

namespace App\Livewire\StockTransfers;

use App\Enums\StockTransferStatus;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?int $source_warehouse_id = null;

    public ?int $destination_shop_id = null;

    public string $notes = '';

    public array $items = [
        ['product_id' => null, 'quantity' => '1.000'],
    ];

    public function mount(): void
    {
        $this->authorize('create', StockTransfer::class);
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'quantity' => '1.000'];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->addItem();
        }
    }

    public function save()
    {
        $validated = $this->validate([
            'source_warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'destination_shop_id' => ['required', 'integer', Rule::exists('shops', 'id')],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        DB::transaction(function () use ($validated): void {
            $transfer = StockTransfer::query()->create([
                'source_warehouse_id' => $validated['source_warehouse_id'],
                'destination_shop_id' => $validated['destination_shop_id'],
                'status' => StockTransferStatus::Pending,
                'initiated_by' => Auth::id(),
                'notes' => $validated['notes'],
            ]);

            foreach ($validated['items'] as $item) {
                $warehouseStock = WarehouseStock::query()
                    ->where('warehouse_id', $validated['source_warehouse_id'])
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (! $warehouseStock || (float) $warehouseStock->quantity < (float) $item['quantity']) {
                    throw new \RuntimeException('Selected warehouse does not have enough stock for one or more items.');
                }

                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }
        });

        session()->flash('status', 'Transfer draft created.');

        return redirect()->route('stock-transfers.index');
    }

    public function getWarehouseOptionsProperty()
    {
        return Warehouse::query()->orderBy('name')->get();
    }

    public function getShopOptionsProperty()
    {
        return Shop::query()->orderBy('name')->get();
    }

    public function getProductOptionsProperty()
    {
        return Product::query()->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.stock-transfers.form-page');
    }
}