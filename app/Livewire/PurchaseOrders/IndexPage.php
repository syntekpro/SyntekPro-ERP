<?php

namespace App\Livewire\PurchaseOrders;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use App\Services\Purchasing\PurchaseOrderReceivingService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    /** @var array<int, array<int, array{purchase_order_item_id:int, quantity_received:string}>> */
    public array $receiveLines = [];

    public function mount(): void
    {
        $this->authorize('viewAny', PurchaseOrder::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function submit(int $purchaseOrderId): void
    {
        $purchaseOrder = PurchaseOrder::query()->findOrFail($purchaseOrderId);

        $this->authorize('submit', $purchaseOrder);

        if (! $purchaseOrder->items()->exists()) {
            throw new \RuntimeException('Purchase order must include at least one item before submission.');
        }

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Submitted,
            'submitted_at' => now(),
        ]);

        session()->flash('status', 'Purchase order submitted.');
    }

    public function receive(int $purchaseOrderId, PurchaseOrderReceivingService $receivingService): void
    {
        $purchaseOrder = PurchaseOrder::query()->with('items')->findOrFail($purchaseOrderId);

        $this->authorize('receive', $purchaseOrder);

        $inputLines = $this->receiveLines[$purchaseOrderId] ?? [];

        if ($inputLines === []) {
            $inputLines = $purchaseOrder->items->map(fn ($item) => [
                'purchase_order_item_id' => $item->id,
                'quantity_received' => (string) round((float) $item->quantity_ordered - (float) $item->quantity_received, 3),
            ])->all();
        }

        $receivingService->receive($purchaseOrder, $inputLines, Auth::id());

        unset($this->receiveLines[$purchaseOrderId]);
        session()->flash('status', 'PO receipt posted: warehouse stock updated and supplier bill created.');
    }

    public function close(int $purchaseOrderId): void
    {
        $purchaseOrder = PurchaseOrder::query()->findOrFail($purchaseOrderId);

        $this->authorize('close', $purchaseOrder);

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Closed,
            'closed_at' => now(),
        ]);

        session()->flash('status', 'Purchase order closed.');
    }

    public function render()
    {
        $purchaseOrders = PurchaseOrder::query()
            ->with(['supplier', 'warehouse', 'items.product'])
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->where('po_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('supplier', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.purchase-orders.index-page', compact('purchaseOrders'));
    }
}
