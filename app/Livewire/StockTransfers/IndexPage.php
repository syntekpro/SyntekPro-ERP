<?php

namespace App\Livewire\StockTransfers;

use App\Enums\StockTransferStatus;
use App\Models\ShopStock;
use App\Models\StockTransfer;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class IndexPage extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', StockTransfer::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function markInTransit(int $transferId): void
    {
        $transfer = StockTransfer::query()->with('items')->findOrFail($transferId);

        $this->authorize('markInTransit', $transfer);

        $transfer->update([
            'status' => StockTransferStatus::InTransit,
            'dispatched_at' => now(),
        ]);

        session()->flash('status', 'Transfer marked in transit.');
    }

    public function receive(int $transferId): void
    {
        $transfer = StockTransfer::query()->with('items')->findOrFail($transferId);

        $this->authorize('receive', $transfer);

        DB::transaction(function () use ($transfer): void {
            foreach ($transfer->items as $item) {
                $warehouseStock = WarehouseStock::query()
                    ->where('warehouse_id', $transfer->source_warehouse_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((float) $warehouseStock->quantity < (float) $item->quantity) {
                    throw new \RuntimeException('Insufficient warehouse stock to receive this transfer.');
                }

                $warehouseStock->update([
                    'quantity' => (float) $warehouseStock->quantity - (float) $item->quantity,
                ]);

                $shopStock = ShopStock::query()
                    ->forAllShops()
                    ->where('shop_id', $transfer->destination_shop_id)
                    ->where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($shopStock) {
                    $shopStock->update([
                        'quantity' => (float) $shopStock->quantity + (float) $item->quantity,
                    ]);
                } else {
                    ShopStock::query()->forAllShops()->create([
                        'shop_id' => $transfer->destination_shop_id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                    ]);
                }
            }

            $transfer->update([
                'status' => StockTransferStatus::Received,
                'received_by' => Auth::id(),
                'received_at' => now(),
            ]);
        });

        session()->flash('status', 'Transfer received and stock updated.');
    }

    public function render()
    {
        $user = Auth::user();

        $transfers = StockTransfer::query()
            ->with(['warehouse', 'destinationShop', 'items.product'])
            ->visibleTo($user)
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner
                        ->whereHas('warehouse', fn ($warehouse) => $warehouse->where('name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('destinationShop', fn ($shop) => $shop->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.stock-transfers.index-page', compact('transfers'));
    }
}