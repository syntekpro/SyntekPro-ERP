<?php

namespace Tests\Feature;

use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\User;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosSaleSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_the_pos_shell_for_a_shop_assigned_cashier(): void
    {
        $shop = Shop::query()->create([
            'name' => 'Central Shop',
            'slug' => 'central-shop',
            'is_active' => true,
        ]);

        $user = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
        ]);

        $this->actingAs($user)
            ->get(route('pos.sales'))
            ->assertOk()
            ->assertSee('Offline cashier')
            ->assertSee('pos-bootstrap', false);
    }

    public function test_syncs_queued_sales_and_decrements_shop_stock_once(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create([
            'name' => 'Central Shop',
            'slug' => 'central-shop',
            'is_active' => true,
        ]);

        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
        ]);

        $product = Product::query()->create([
            'name' => 'Premium Rice 5kg',
            'sku' => 'RICE-5KG',
            'barcode' => '6291234567890',
            'price' => 50,
            'cost_price' => 32,
            'vat_rate' => 7.5,
            'is_active' => true,
        ]);

        ShopStock::query()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $payload = [
            'sales' => [[
                'idempotency_key' => 'sale-001',
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '100.00',
                'vat_total' => '7.50',
                'total' => '107.50',
                'items' => [[
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => '2.000',
                    'unit_price' => '50.00',
                    'vat_rate' => '7.50',
                    'vat_amount' => '7.50',
                    'line_total' => '107.50',
                ]],
            ]],
        ];

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Synced->value);

        $this->assertDatabaseHas('sales', [
            'shop_id' => $shop->id,
            'cashier_id' => $cashier->id,
            'idempotency_key' => 'sale-001',
            'status' => SaleStatus::Synced->value,
        ]);

        $sale = \App\Models\Sale::query()->where('idempotency_key', 'sale-001')->firstOrFail();

        $this->assertNotNull($sale->zatca_qr_payload);
        $this->assertNotNull($sale->invoice_uuid);
        $this->assertNotNull($sale->invoice_hash);

        $decodedQr = base64_decode((string) $sale->zatca_qr_payload, true);

        $this->assertNotFalse($decodedQr);
        $this->assertStringContainsString('Central Shop', (string) $decodedQr);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'unit_cost' => '32.00',
        ]);

        $this->assertDatabaseHas('shop_stock', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $journalEntry = JournalEntry::query()
            ->forAllShops()
            ->where('sale_id', $sale->id)
            ->firstOrFail();

        $this->assertDatabaseHas('journal_entries', [
            'id' => $journalEntry->id,
            'shop_id' => $shop->id,
            'sale_id' => $sale->id,
            'source' => 'pos_sale',
        ]);

        $cashAccountId = (int) Account::query()->where('code', '1010')->value('id');
        $salesRevenueAccountId = (int) Account::query()->where('code', '4100')->value('id');
        $vatPayableAccountId = (int) Account::query()->where('code', '2200')->value('id');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $cashAccountId,
            'debit' => '107.50',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $salesRevenueAccountId,
            'debit' => '0.00',
            'credit' => '100.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $vatPayableAccountId,
            'debit' => '0.00',
            'credit' => '7.50',
        ]);

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Duplicate->value);
    }

    public function test_rejects_sync_when_shop_stock_is_insufficient(): void
    {
        $shop = Shop::query()->create([
            'name' => 'Central Shop',
            'slug' => 'central-shop',
            'is_active' => true,
        ]);

        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
        ]);

        $product = Product::query()->create([
            'name' => 'Premium Rice 5kg',
            'sku' => 'RICE-5KG',
            'barcode' => '6291234567890',
            'price' => 50,
            'cost_price' => 32,
            'vat_rate' => 7.5,
            'is_active' => true,
        ]);

        ShopStock::query()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $payload = [
            'sales' => [[
                'idempotency_key' => 'sale-002',
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '100.00',
                'vat_total' => '7.50',
                'total' => '107.50',
                'items' => [[
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => '2.000',
                    'unit_price' => '50.00',
                    'vat_rate' => '7.50',
                    'vat_amount' => '7.50',
                    'line_total' => '107.50',
                ]],
            ]],
        ];

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Rejected->value);

        $this->assertDatabaseMissing('sales', [
            'idempotency_key' => 'sale-002',
        ]);

        $this->assertDatabaseHas('shop_stock', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);
    }
}