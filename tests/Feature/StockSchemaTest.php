<?php

namespace Tests\Feature;

use App\Enums\StockTransferStatus;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StockSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_hub_owned_tables_do_not_gain_shop_scope_columns(): void
    {
        $this->assertFalse(Schema::hasColumn('products', 'shop_id'));
        $this->assertFalse(Schema::hasColumn('warehouses', 'shop_id'));
    }

    public function test_stock_schema_uses_separate_warehouse_and_shop_stock_tables(): void
    {
        $this->assertTrue(Schema::hasTable('warehouse_stock'));
        $this->assertTrue(Schema::hasTable('shop_stock'));
        $this->assertTrue(Schema::hasTable('stock_transfers'));
        $this->assertTrue(Schema::hasTable('stock_transfer_items'));
        $this->assertFalse(Schema::hasTable('stock'));
    }

    public function test_stock_transfer_status_defaults_to_pending(): void
    {
        $shop = Shop::query()->create([
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'name' => 'Central Warehouse',
            'code' => 'WH-003',
        ]);

        $product = Product::query()->create([
            'name' => 'Thermal Roll',
            'sku' => 'SKU-003',
            'price' => 9.50,
        ]);

        $transfer = StockTransfer::query()->create([
            'source_warehouse_id' => $warehouse->id,
            'destination_shop_id' => $shop->id,
        ]);

        $transfer->items()->create([
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->assertSame(StockTransferStatus::Pending, $transfer->fresh()->status);
    }
}