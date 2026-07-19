<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SaleStatus;
use App\Models\Account;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\PriceCategory;
use App\Models\Product;
use App\Models\ProductUnitConversion;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Purchasing\PurchaseOrderReceivingService;
use App\Services\Returns\CreditNoteService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase12UnitsAndPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchasing_in_boxes_and_selling_in_pieces_keeps_stock_and_average_cost_in_base_units(): void
    {
        [$shop, $cashier] = $this->seedAccountingAndShop();
        $warehouse = Warehouse::query()->create(['name' => 'Central Warehouse', 'code' => 'WH-C', 'is_active' => true]);
        $supplier = Supplier::query()->create(['name' => 'Unit Supplier', 'code' => 'SUP-UOM', 'payment_terms_days' => 30, 'is_active' => true]);
        $pcs = Unit::query()->where('code', 'PCS')->firstOrFail();
        $box = Unit::query()->create(['code' => 'BOX', 'name' => 'Box', 'is_active' => true]);

        $product = Product::query()->create([
            'name' => 'Sparkling Water',
            'sku' => 'WATER-PCS',
            'base_unit_id' => $pcs->id,
            'price' => 15,
            'cost_price' => 0,
            'average_cost' => 0,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        ProductUnitConversion::query()->create([
            'product_id' => $product->id,
            'unit_id' => $box->id,
            'conversion_factor' => 12,
        ]);

        $po = PurchaseOrder::query()->create([
            'po_number' => 'PO-UOM-001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseOrderStatus::Submitted,
            'created_by' => $cashier->id,
        ]);

        $poItem = $po->items()->create([
            'product_id' => $product->id,
            'unit_id' => $box->id,
            'quantity_ordered' => 2,
            'unit_cost' => 120,
            'vat_rate' => 15,
        ]);

        $bill = app(PurchaseOrderReceivingService::class)->receive($po, [[
            'purchase_order_item_id' => $poItem->id,
            'quantity_received' => 2,
            'unit_id' => $box->id,
        ]], $cashier->id);

        $this->assertDatabaseHas('warehouse_stock', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => '24.000',
        ]);
        $this->assertDatabaseHas('supplier_bill_items', [
            'supplier_bill_id' => $bill->id,
            'product_id' => $product->id,
            'quantity' => '2.000',
            'base_quantity' => '24.000',
            'unit_cost' => '120.00',
            'net_amount' => '240.00',
        ]);
        $this->assertSame('10.0000', $product->fresh()->average_cost);

        ShopStock::query()->create(['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => 24]);

        $this->syncSale($cashier, $shop, [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => null,
            'unit_id' => $pcs->id,
            'quantity' => '5.000',
            'unit_price' => '15.00',
        ]], 'phase12-uom-sale');

        $sale = Sale::query()->where('idempotency_key', 'phase12-uom-sale')->firstOrFail();
        $this->assertDatabaseHas('shop_stock', ['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => '19.000']);
        $this->assertDatabaseHas('sale_items', ['sale_id' => $sale->id, 'quantity' => '5.000', 'base_quantity' => '5.000', 'unit_cost' => '10.00']);

        $cogsAccountId = (int) Account::query()->where('code', '5100')->value('id');
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $sale->journalEntry?->id ?? JournalEntry::query()->where('sale_id', $sale->id)->value('id'),
            'account_id' => $cogsAccountId,
            'debit' => '50.00',
            'credit' => '0.00',
        ]);
    }

    public function test_return_against_multi_unit_sale_validates_and_processes_in_base_units(): void
    {
        [$shop, $cashier] = $this->seedAccountingAndShop();
        $pcs = Unit::query()->where('code', 'PCS')->firstOrFail();
        $box = Unit::query()->create(['code' => 'BOX', 'name' => 'Box', 'is_active' => true]);

        $product = Product::query()->create(['name' => 'Return Case Item', 'sku' => 'RET-BOX', 'base_unit_id' => $pcs->id, 'price' => 10, 'cost_price' => 4, 'average_cost' => 4, 'vat_rate' => 15, 'is_active' => true]);
        ProductUnitConversion::query()->create(['product_id' => $product->id, 'unit_id' => $box->id, 'conversion_factor' => 12]);
        ShopStock::query()->create(['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => 24]);

        $this->syncSale($cashier, $shop, [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => null,
            'unit_id' => $box->id,
            'quantity' => '1.000',
            'unit_price' => '120.00',
        ]], 'phase12-return-sale');

        $sale = Sale::query()->with('items')->where('idempotency_key', 'phase12-return-sale')->firstOrFail();

        $creditNote = app(CreditNoteService::class)->record($sale->id, now()->toDateString(), [[
            'sale_item_id' => $sale->items->first()->id,
            'unit_id' => $pcs->id,
            'quantity' => 6,
            'condition' => 'sellable',
        ]], null, $cashier->id);

        $this->assertInstanceOf(CreditNote::class, $creditNote);
        $this->assertDatabaseHas('credit_note_items', [
            'credit_note_id' => $creditNote->id,
            'quantity' => '6.000',
            'base_quantity' => '6.000',
            'net_amount' => '60.00',
        ]);
        $this->assertDatabaseHas('shop_stock', ['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => '18.000']);
    }

    public function test_pos_price_resolution_uses_customer_category_then_shop_category_then_product_base_price(): void
    {
        [$shop, $cashier] = $this->seedAccountingAndShop();
        $wholesale = PriceCategory::query()->create(['name' => 'Wholesale', 'is_active' => true]);
        $vip = PriceCategory::query()->create(['name' => 'VIP', 'is_active' => true]);
        $product = Product::query()->create(['name' => 'Tiered Price Item', 'sku' => 'TIER-1', 'price' => 100, 'cost_price' => 20, 'average_cost' => 20, 'vat_rate' => 15, 'is_active' => true]);
        $product->prices()->create(['price_category_id' => $wholesale->id, 'price' => 80]);
        $product->prices()->create(['price_category_id' => $vip->id, 'price' => 70]);

        $customer = Customer::query()->create(['name' => 'VIP Buyer', 'code' => 'VIP-1', 'payment_terms_days' => 30, 'default_price_category_id' => $vip->id, 'is_active' => true]);
        ShopStock::query()->create(['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => 10]);

        $this->syncSale($cashier, $shop, [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => null,
            'quantity' => '1.000',
            'unit_price' => '999.00',
        ]], 'phase12-price-customer', 'credit_account', $customer->id);
        $this->assertDatabaseHas('sales', ['idempotency_key' => 'phase12-price-customer', 'subtotal' => '70.00']);

        $shop->update(['default_price_category_id' => $wholesale->id]);
        $this->syncSale($cashier, $shop->fresh(), [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => null,
            'quantity' => '1.000',
            'unit_price' => '999.00',
        ]], 'phase12-price-shop');
        $this->assertDatabaseHas('sales', ['idempotency_key' => 'phase12-price-shop', 'subtotal' => '80.00']);

        $shop->update(['default_price_category_id' => null]);
        $this->syncSale($cashier, $shop->fresh(), [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => null,
            'quantity' => '1.000',
            'unit_price' => '999.00',
        ]], 'phase12-price-base');
        $this->assertDatabaseHas('sales', ['idempotency_key' => 'phase12-price-base', 'subtotal' => '100.00']);
    }

    public function test_default_unit_and_no_price_override_sale_matches_pre_phase12_behavior(): void
    {
        [$shop, $cashier] = $this->seedAccountingAndShop();
        $product = Product::query()->create(['name' => 'Default Behavior Item', 'sku' => 'DEF-1', 'price' => 50, 'cost_price' => 32, 'average_cost' => 32, 'vat_rate' => 15, 'is_active' => true]);
        ShopStock::query()->create(['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => 3]);

        $this->syncSale($cashier, $shop, [[
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'barcode' => null,
            'quantity' => '2.000',
            'unit_price' => '50.00',
        ]], 'phase12-default-sale');

        $sale = Sale::query()->where('idempotency_key', 'phase12-default-sale')->firstOrFail();
        $journalEntryId = (int) JournalEntry::query()->where('sale_id', $sale->id)->value('id');

        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'subtotal' => '100.00', 'vat_total' => '15.00', 'total' => '115.00']);
        $this->assertDatabaseHas('sale_items', ['sale_id' => $sale->id, 'quantity' => '2.000', 'base_quantity' => '2.000', 'unit_price' => '50.00', 'unit_cost' => '32.00']);
        $this->assertDatabaseHas('shop_stock', ['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => '1.000']);
        $this->assertDatabaseHas('journal_entry_lines', ['journal_entry_id' => $journalEntryId, 'account_id' => Account::query()->where('code', '1010')->value('id'), 'debit' => '115.00', 'credit' => '0.00']);
        $this->assertDatabaseHas('journal_entry_lines', ['journal_entry_id' => $journalEntryId, 'account_id' => Account::query()->where('code', '4100')->value('id'), 'debit' => '0.00', 'credit' => '100.00']);
        $this->assertDatabaseHas('journal_entry_lines', ['journal_entry_id' => $journalEntryId, 'account_id' => Account::query()->where('code', '5100')->value('id'), 'debit' => '64.00', 'credit' => '0.00']);
    }

    protected function seedAccountingAndShop(): array
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create(['name' => 'Phase 12 Shop', 'slug' => 'phase-12-shop', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => 'cashier', 'shop_id' => $shop->id]);

        return [$shop, $cashier];
    }

    protected function syncSale(User $cashier, Shop $shop, array $items, string $idempotencyKey, string $paymentMethod = 'cash', ?int $customerId = null): void
    {
        $subtotal = collect($items)->sum(fn (array $item) => (float) $item['quantity'] * (float) $item['unit_price']);
        $vatTotal = round($subtotal * 0.15, 2);

        $payload = [
            'sales' => [[
                'idempotency_key' => $idempotencyKey,
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'vat_total' => number_format($vatTotal, 2, '.', ''),
                'total' => number_format($subtotal + $vatTotal, 2, '.', ''),
                'payment_method' => $paymentMethod,
                'customer_id' => $customerId,
                'items' => array_map(fn (array $item) => $item + [
                    'vat_rate' => '15.00',
                    'vat_amount' => number_format((float) $item['quantity'] * (float) $item['unit_price'] * 0.15, 2, '.', ''),
                    'line_total' => number_format((float) $item['quantity'] * (float) $item['unit_price'] * 1.15, 2, '.', ''),
                ], $items),
            ]],
        ];

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Synced->value);
    }
}