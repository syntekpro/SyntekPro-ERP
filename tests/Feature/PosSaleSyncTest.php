<?php

namespace Tests\Feature;

use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\User;
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

        $this->assertDatabaseHas('shop_stock', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 1,
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