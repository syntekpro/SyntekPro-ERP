<?php

namespace Tests\Feature;

use App\Enums\StockTransferStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_cannot_access_hub_crud_resources(): void
    {
        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
        ]);

        $product = Product::query()->create([
            'name' => 'Scanner',
            'sku' => 'SKU-001',
            'price' => 99.99,
        ]);

        $warehouse = Warehouse::query()->create([
            'name' => 'Central Warehouse',
            'code' => 'WH-001',
        ]);

        $this->assertFalse(Gate::forUser($cashier)->allows('viewAny', Product::class));
        $this->assertFalse(Gate::forUser($cashier)->allows('create', Product::class));
        $this->assertFalse(Gate::forUser($cashier)->allows('update', $product));
        $this->assertFalse(Gate::forUser($cashier)->allows('viewAny', Warehouse::class));
        $this->assertFalse(Gate::forUser($cashier)->allows('update', $warehouse));
    }

    public function test_shop_manager_can_only_manage_stock_for_their_own_shop(): void
    {
        $shopA = Shop::query()->create([
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'is_active' => true,
        ]);

        $shopB = Shop::query()->create([
            'name' => 'Shop B',
            'slug' => 'shop-b',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'name' => 'Receipt Paper',
            'sku' => 'SKU-002',
            'price' => 15.00,
        ]);

        $managerA = User::factory()->create([
            'email' => 'manager-a@example.com',
            'role' => UserRole::ShopManager,
            'shop_id' => $shopA->id,
        ]);

        $managerB = User::factory()->create([
            'email' => 'manager-b@example.com',
            'role' => UserRole::ShopManager,
            'shop_id' => $shopB->id,
        ]);

        $shopStock = ShopStock::query()->create([
            'shop_id' => $shopA->id,
            'product_id' => $product->id,
            'quantity' => 12,
        ]);

        $this->assertTrue(Gate::forUser($managerA)->allows('view', $shopStock));
        $this->assertTrue(Gate::forUser($managerA)->allows('update', $shopStock));
        $this->assertFalse(Gate::forUser($managerB)->allows('view', $shopStock));
        $this->assertFalse(Gate::forUser($managerB)->allows('update', $shopStock));
    }

    public function test_stock_transfer_receive_policy_checks_destination_shop_context(): void
    {
        $shopA = Shop::query()->create([
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'is_active' => true,
        ]);

        $shopB = Shop::query()->create([
            'name' => 'Shop B',
            'slug' => 'shop-b',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'name' => 'Central Warehouse',
            'code' => 'WH-002',
        ]);

        $managerA = User::factory()->create([
            'email' => 'manager-a@example.com',
            'role' => UserRole::ShopManager,
            'shop_id' => $shopA->id,
        ]);

        $managerB = User::factory()->create([
            'email' => 'manager-b@example.com',
            'role' => UserRole::ShopManager,
            'shop_id' => $shopB->id,
        ]);

        $cashier = User::factory()->create([
            'email' => 'cashier@example.com',
            'role' => UserRole::Cashier,
            'shop_id' => $shopA->id,
        ]);

        $transfer = StockTransfer::query()->create([
            'source_warehouse_id' => $warehouse->id,
            'destination_shop_id' => $shopA->id,
            'status' => StockTransferStatus::InTransit,
            'initiated_by' => $managerA->id,
        ]);

        $this->assertTrue(Gate::forUser($managerA)->allows('receive', $transfer));
        $this->assertFalse(Gate::forUser($managerB)->allows('receive', $transfer));
        $this->assertFalse(Gate::forUser($cashier)->allows('receive', $transfer));
    }
}