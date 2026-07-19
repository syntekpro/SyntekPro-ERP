<?php

namespace App\Livewire\PurchaseOrders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\Numbering\DocumentNumberService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class FormPage extends Component
{
    use AuthorizesRequests;

    public ?PurchaseOrder $purchaseOrder = null;

    public ?int $supplier_id = null;

    public ?int $warehouse_id = null;

    public string $notes = '';

    public array $items = [
        ['product_id' => null, 'quantity_ordered' => '1.000', 'unit_cost' => '0.00', 'vat_rate' => '15.00'],
    ];

    public function mount(?PurchaseOrder $purchaseOrder = null): void
    {
        $this->purchaseOrder = $purchaseOrder?->exists ? $purchaseOrder->load('items') : null;

        if ($this->purchaseOrder) {
            $this->authorize('update', $this->purchaseOrder);

            $this->supplier_id = $this->purchaseOrder->supplier_id;
            $this->warehouse_id = $this->purchaseOrder->warehouse_id;
            $this->notes = (string) ($this->purchaseOrder->notes ?? '');
            $this->items = $this->purchaseOrder->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity_ordered' => number_format((float) $item->quantity_ordered, 3, '.', ''),
                'unit_cost' => number_format((float) $item->unit_cost, 2, '.', ''),
                'vat_rate' => number_format((float) $item->vat_rate, 2, '.', ''),
            ])->all();

            return;
        }

        $this->authorize('create', PurchaseOrder::class);
    }

    public function addItem(): void
    {
        $this->items[] = ['product_id' => null, 'quantity_ordered' => '1.000', 'unit_cost' => '0.00', 'vat_rate' => '15.00'];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->addItem();
        }
    }

    public function save(DocumentNumberService $documentNumberService)
    {
        $validated = $this->validate([
            'supplier_id' => ['required', 'integer', Rule::exists('suppliers', 'id')],
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', Rule::exists('products', 'id')],
            'items.*.quantity_ordered' => ['required', 'numeric', 'min:0.001'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
            'items.*.vat_rate' => ['required', 'numeric', 'min:0'],
        ]);

        if ($this->purchaseOrder) {
            $this->purchaseOrder->update([
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'notes' => $validated['notes'] === '' ? null : $validated['notes'],
            ]);

            $this->purchaseOrder->items()->delete();
            $purchaseOrder = $this->purchaseOrder;
            session()->flash('status', 'Purchase order updated.');
        } else {
            $purchaseOrder = DB::transaction(function () use ($validated, $documentNumberService): PurchaseOrder {
                return PurchaseOrder::query()->create([
                    'po_number' => $documentNumberService->next('purchase_orders'),
                    'supplier_id' => $validated['supplier_id'],
                    'warehouse_id' => $validated['warehouse_id'],
                    'notes' => $validated['notes'] === '' ? null : $validated['notes'],
                    'created_by' => Auth::id(),
                ]);
            });

            session()->flash('status', 'Purchase order created.');
        }

        foreach ($validated['items'] as $item) {
            $purchaseOrder->items()->create([
                'product_id' => $item['product_id'],
                'quantity_ordered' => $item['quantity_ordered'],
                'unit_cost' => $item['unit_cost'],
                'vat_rate' => $item['vat_rate'],
            ]);
        }

        return redirect()->route('purchase-orders.index');
    }

    public function getSupplierOptionsProperty()
    {
        return Supplier::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getWarehouseOptionsProperty()
    {
        return Warehouse::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function getProductOptionsProperty()
    {
        return Product::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.purchase-orders.form-page');
    }
}
