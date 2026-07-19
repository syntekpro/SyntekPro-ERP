<?php

namespace Tests\Feature;

use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Livewire\Settings\SettingsPage;
use App\Models\Account;
use App\Models\BusinessSetting;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\User;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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
                'payment_method' => 'cash',
                'customer_id' => null,
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
            'debit' => '115.00',
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
            'credit' => '15.00',
        ]);

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Duplicate->value);
    }

    public function test_vat_disabled_sales_post_no_vat_lines(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update([
            'vat_enabled' => false,
            'vat_rate' => 15,
        ]);

        $shop = Shop::query()->create(['name' => 'No VAT Shop', 'slug' => 'no-vat-shop', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'shop_id' => $shop->id]);
        $product = Product::query()->create(['name' => 'Zero VAT Item', 'sku' => 'ZERO-VAT', 'price' => 100, 'cost_price' => 40, 'vat_rate' => 15, 'is_active' => true]);

        ShopStock::query()->create(['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => 2]);

        $payload = [
            'sales' => [[
                'idempotency_key' => 'sale-vat-disabled',
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '100.00',
                'vat_total' => '15.00',
                'total' => '115.00',
                'payment_method' => 'cash',
                'customer_id' => null,
                'items' => [[
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => null,
                    'quantity' => '1.000',
                    'unit_price' => '100.00',
                    'vat_rate' => '15.00',
                    'vat_amount' => '15.00',
                    'line_total' => '115.00',
                ]],
            ]],
        ];

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Synced->value);

        $sale = \App\Models\Sale::query()->where('idempotency_key', 'sale-vat-disabled')->firstOrFail();
        $journalEntry = JournalEntry::query()->forAllShops()->where('sale_id', $sale->id)->firstOrFail();
        $vatPayableAccountId = (int) Account::query()->where('code', '2200')->value('id');

        $this->assertSame('0.00', $sale->vat_total);
        $this->assertSame('100.00', $sale->total);
        $this->assertDatabaseMissing('journal_entry_lines', [
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $vatPayableAccountId,
        ]);
    }

    public function test_excise_tax_posts_correctly_alongside_vat(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update([
            'vat_enabled' => true,
            'vat_rate' => 15,
        ]);

        $shop = Shop::query()->create(['name' => 'Excise Shop', 'slug' => 'excise-shop', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'shop_id' => $shop->id]);
        $product = Product::query()->create([
            'name' => 'Excise Item',
            'sku' => 'EXCISE-1',
            'price' => 100,
            'cost_price' => 40,
            'vat_rate' => 15,
            'is_excise_applicable' => true,
            'excise_rate' => 50,
            'is_active' => true,
        ]);

        ShopStock::query()->create(['shop_id' => $shop->id, 'product_id' => $product->id, 'quantity' => 2]);

        $payload = [
            'sales' => [[
                'idempotency_key' => 'sale-excise',
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '100.00',
                'vat_total' => '15.00',
                'total' => '115.00',
                'payment_method' => 'cash',
                'customer_id' => null,
                'items' => [[
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => null,
                    'quantity' => '1.000',
                    'unit_price' => '100.00',
                    'vat_rate' => '15.00',
                    'vat_amount' => '15.00',
                    'line_total' => '115.00',
                ]],
            ]],
        ];

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Synced->value);

        $sale = \App\Models\Sale::query()->where('idempotency_key', 'sale-excise')->firstOrFail();
        $journalEntry = JournalEntry::query()->forAllShops()->where('sale_id', $sale->id)->firstOrFail();
        $exciseTaxPayableAccountId = (int) Account::query()->where('code', '2300')->value('id');

        $this->assertSame('15.00', $sale->vat_total);
        $this->assertSame('50.00', $sale->excise_total);
        $this->assertSame('165.00', $sale->total);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $exciseTaxPayableAccountId,
            'debit' => '0.00',
            'credit' => '50.00',
        ]);
    }

    public function test_settings_legal_name_and_vat_number_are_reflected_in_new_zatca_qr_codes(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create([
            'name' => 'Fallback Shop',
            'slug' => 'fallback-shop',
            'is_active' => true,
        ]);

        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
        ]);

        $product = Product::query()->create([
            'name' => 'Excise Free Item',
            'sku' => 'NO-EXCISE-1',
            'price' => 100,
            'cost_price' => 40,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        ShopStock::query()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(SettingsPage::class)
            ->set('settings.legal_name', 'Configured Legal Entity LLC')
            ->set('settings.vat_number', '312345678900003')
            ->call('saveGeneral')
            ->assertHasNoErrors();

        $payload = [
            'sales' => [[
                'idempotency_key' => 'sale-settings-qr',
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '100.00',
                'vat_total' => '15.00',
                'total' => '115.00',
                'payment_method' => 'cash',
                'customer_id' => null,
                'items' => [[
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => null,
                    'quantity' => '1.000',
                    'unit_price' => '100.00',
                    'vat_rate' => '15.00',
                    'vat_amount' => '15.00',
                    'line_total' => '115.00',
                ]],
            ]],
        ];

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Synced->value);

        $sale = \App\Models\Sale::query()->where('idempotency_key', 'sale-settings-qr')->firstOrFail();
        $decodedQr = base64_decode((string) $sale->zatca_qr_payload, true);

        $this->assertNotFalse($decodedQr);
        $this->assertStringContainsString('Configured Legal Entity LLC', (string) $decodedQr);
        $this->assertStringContainsString('312345678900003', (string) $decodedQr);
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
                'payment_method' => 'cash',
                'customer_id' => null,
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