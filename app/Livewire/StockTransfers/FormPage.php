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

        $warnings = $this->buildReservationWarnings($validated);

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

        if ($warnings !== []) {
            session()->flash('warning', implode(' ', $warnings));
        }

        session()->flash('status', 'Transfer draft created.');

        return redirect()->route('stock-transfers.index');
    }

    public function getWarehouseOptionsProperty()
    {
        return Warehouse::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getShopOptionsProperty()
    {
        return Shop::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getProductOptionsProperty()
    {
        return Product::query()->where('is_active', true)->orderBy('name')->get();
    }

    protected function buildReservationWarnings(array $validated): array
    {
        $requested = [];

        foreach ($validated['items'] as $item) {
            $productId = (int) $item['product_id'];
            $requested[$productId] = ($requested[$productId] ?? 0) + (float) $item['quantity'];
        }

        $reservedByProduct = DB::table('stock_transfer_items')
            ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_items.stock_transfer_id')
            ->where('stock_transfers.source_warehouse_id', $validated['source_warehouse_id'])
            ->whereIn('stock_transfers.status', [
                StockTransferStatus::Pending->value,
                StockTransferStatus::InTransit->value,
            ])
            ->selectRaw('stock_transfer_items.product_id as product_id, SUM(stock_transfer_items.quantity) as reserved_quantity')
            ->groupBy('stock_transfer_items.product_id')
            ->pluck('reserved_quantity', 'product_id');

        $warnings = [];

        foreach ($requested as $productId => $qty) {
            $stock = WarehouseStock::query()
                ->where('warehouse_id', $validated['source_warehouse_id'])
                ->where('product_id', $productId)
                ->first();

            $current = (float) ($stock?->quantity ?? 0);
            $reserved = (float) ($reservedByProduct[$productId] ?? 0);
            $availableAfterReservation = $current - $reserved;

            if ($qty > $availableAfterReservation) {
                $productName = Product::query()->whereKey($productId)->value('name') ?? '#'.$productId;
                $warnings[] = "Reservation warning: {$productName} requests {$qty} while projected available is {$availableAfterReservation}.";
            }
        }

        return $warnings;
    }

    public function render()
    {
        return view('livewire.stock-transfers.form-page');
    }
}