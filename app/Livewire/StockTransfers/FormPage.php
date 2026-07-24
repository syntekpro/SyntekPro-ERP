<?php

namespace App\Livewire\StockTransfers;

use App\Enums\StockTransferStatus;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\Inventory\UnitConversionService;
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
        ['product_id' => null, 'unit_id' => null, 'quantity' => '1.000'],
    ];

    public array $productSearch = [];

    public function mount(): void
    {
        $this->authorize('create', StockTransfer::class);
        $this->syncProductSearchFromItems();
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'unit_id' => null, 'quantity' => '1.000'];
        $this->productSearch[] = '';
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        unset($this->productSearch[$index]);
        $this->items = array_values($this->items);
        $this->productSearch = array_values($this->productSearch);

        if ($this->items === []) {
            $this->addItem();
        }
    }

    public function selectProduct(int $index, int $productId): void
    {
        $product = Product::query()
            ->where('is_active', true)
            ->select(['id', 'name'])
            ->findOrFail($productId);

        $this->items[$index]['product_id'] = $product->id;
        $this->items[$index]['unit_id'] = null;
        $this->productSearch[$index] = $product->name;
    }

    public function productResults(int $index)
    {
        $search = trim((string) ($this->productSearch[$index] ?? ''));

        if ($search === '') {
            return collect();
        }

        $term = '%'.$search.'%';

        return Product::query()
            ->where('is_active', true)
            ->where(function ($query) use ($term): void {
                $query
                    ->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('barcode', 'like', $term);
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'sku', 'barcode']);
    }

    public function selectedProduct(int $index): ?Product
    {
        $productId = (int) ($this->items[$index]['product_id'] ?? 0);

        if ($productId <= 0) {
            return null;
        }

        return Product::query()
            ->with(['baseUnit', 'unitConversions.unit'])
            ->find($productId);
    }

    public function save(UnitConversionService $unitConversionService)
    {
        $validated = $this->validate([
            'source_warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'destination_shop_id' => ['required', 'integer', Rule::exists('shops', 'id')],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.unit_id' => ['nullable', 'integer', Rule::exists('units', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'min:0.001'],
        ]);

        $preparedItems = $this->prepareItems($validated['items'], $unitConversionService);
        $warnings = $this->buildReservationWarnings($validated, $preparedItems);

        DB::transaction(function () use ($validated, $preparedItems): void {
            $transfer = StockTransfer::query()->create([
                'source_warehouse_id' => $validated['source_warehouse_id'],
                'destination_shop_id' => $validated['destination_shop_id'],
                'status' => StockTransferStatus::Pending,
                'initiated_by' => Auth::id(),
                'notes' => $validated['notes'],
            ]);

            foreach ($preparedItems as $item) {
                $warehouseStock = WarehouseStock::query()
                    ->where('warehouse_id', $validated['source_warehouse_id'])
                    ->where('product_id', $item['product_id'])
                    ->first();

                if (! $warehouseStock || (float) $warehouseStock->quantity < (float) $item['base_quantity']) {
                    throw new \RuntimeException('Selected warehouse does not have enough stock for one or more items.');
                }

                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'unit_id' => $item['unit_id'],
                    'quantity' => $item['quantity'],
                    'base_quantity' => $item['base_quantity'],
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

    protected function prepareItems(array $items, UnitConversionService $unitConversionService): array
    {
        return collect($items)->map(function (array $item) use ($unitConversionService): array {
            $product = Product::query()->findOrFail($item['product_id']);
            $unitId = $item['unit_id'] !== null ? (int) $item['unit_id'] : $unitConversionService->baseUnitId($product);

            return [
                'product_id' => (int) $item['product_id'],
                'unit_id' => $unitId,
                'quantity' => round((float) $item['quantity'], 3),
                'base_quantity' => $unitConversionService->toBaseQuantity($product, (float) $item['quantity'], $unitId),
            ];
        })->all();
    }

    protected function buildReservationWarnings(array $validated, array $preparedItems): array
    {
        $requested = [];

        foreach ($preparedItems as $item) {
            $productId = (int) $item['product_id'];
            $requested[$productId] = ($requested[$productId] ?? 0) + (float) $item['base_quantity'];
        }

        $reservedByProduct = DB::table('stock_transfer_items')
            ->join('stock_transfers', 'stock_transfers.id', '=', 'stock_transfer_items.stock_transfer_id')
            ->where('stock_transfers.source_warehouse_id', $validated['source_warehouse_id'])
            ->whereIn('stock_transfers.status', [
                StockTransferStatus::Pending->value,
                StockTransferStatus::InTransit->value,
            ])
            ->selectRaw('stock_transfer_items.product_id as product_id, SUM(stock_transfer_items.base_quantity) as reserved_quantity')
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

    protected function syncProductSearchFromItems(): void
    {
        $productIds = collect($this->items)
            ->pluck('product_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $productNames = Product::query()
            ->whereIn('id', $productIds)
            ->pluck('name', 'id');

        foreach ($this->items as $index => $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $this->productSearch[$index] = $productId > 0 ? (string) ($productNames[$productId] ?? '') : '';
        }
    }
}