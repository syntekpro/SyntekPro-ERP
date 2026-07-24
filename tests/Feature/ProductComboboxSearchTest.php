<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\PurchaseOrders\FormPage as PurchaseOrderFormPage;
use App\Livewire\StockTransfers\FormPage as StockTransferFormPage;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductComboboxSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_order_combobox_filters_by_name_and_sku_and_still_saves_selected_product(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $supplier = Supplier::query()->create([
            'name' => 'Acme Supplies',
            'code' => 'SUP-ACME',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);
        $warehouse = Warehouse::query()->create(['name' => 'Central', 'code' => 'WH-C', 'is_active' => true]);

        Product::query()->create([
            'name' => 'Scanner',
            'sku' => 'SCN-100',
            'barcode' => '111222333',
            'price' => 100,
            'cost_price' => 70,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        $targetProduct = Product::query()->create([
            'name' => 'Receipt Roll',
            'sku' => 'RLL-200',
            'barcode' => '444555666',
            'price' => 12,
            'cost_price' => 8,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        Livewire::actingAs($admin)
            ->test(PurchaseOrderFormPage::class)
            ->set('supplier_id', $supplier->id)
            ->set('warehouse_id', $warehouse->id)
            ->call('addItem')
            ->set('productSearch.1', 'scan')
            ->assertSee('Scanner')
            ->assertDontSee('Receipt Roll')
            ->set('productSearch.1', 'rll-2')
            ->assertSee('Receipt Roll')
            ->call('removeItem', 1)
            ->set('productSearch.0', 'rll-2')
            ->call('selectProduct', 0, $targetProduct->id)
            ->assertSet('items.0.product_id', $targetProduct->id)
            ->assertSet('productSearch.0', 'Receipt Roll')
            ->set('items.0.quantity_ordered', '2.000')
            ->set('items.0.unit_cost', '8.00')
            ->set('items.0.vat_rate', '15.00')
            ->call('save')
            ->assertRedirect(route('purchase-orders.index'));

        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $targetProduct->id,
            'quantity_ordered' => '2.000',
        ]);
    }

    public function test_stock_transfer_combobox_filters_by_name_and_sku_and_still_saves_selected_product(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $shop = Shop::query()->create(['name' => 'Riyadh', 'slug' => 'riyadh', 'is_active' => true]);
        $warehouse = Warehouse::query()->create(['name' => 'Central', 'code' => 'WH-C', 'is_active' => true]);

        Product::query()->create([
            'name' => 'Scanner',
            'sku' => 'SCN-100',
            'barcode' => '111222333',
            'price' => 100,
            'cost_price' => 70,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        $targetProduct = Product::query()->create([
            'name' => 'Receipt Roll',
            'sku' => 'RLL-200',
            'barcode' => '444555666',
            'price' => 12,
            'cost_price' => 8,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        WarehouseStock::query()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $targetProduct->id,
            'quantity' => 10,
        ]);

        Livewire::actingAs($admin)
            ->test(StockTransferFormPage::class)
            ->set('source_warehouse_id', $warehouse->id)
            ->set('destination_shop_id', $shop->id)
            ->call('addItem')
            ->set('productSearch.1', 'scan')
            ->assertSee('Scanner')
            ->assertDontSee('Receipt Roll')
            ->set('productSearch.1', 'rll-2')
            ->assertSee('Receipt Roll')
            ->call('removeItem', 1)
            ->set('productSearch.0', 'rll-2')
            ->call('selectProduct', 0, $targetProduct->id)
            ->assertSet('items.0.product_id', $targetProduct->id)
            ->assertSet('productSearch.0', 'Receipt Roll')
            ->set('items.0.quantity', '2.000')
            ->call('save')
            ->assertRedirect(route('stock-transfers.index'));

        $this->assertDatabaseHas('stock_transfer_items', [
            'product_id' => $targetProduct->id,
            'quantity' => '2.000',
        ]);
    }
}
