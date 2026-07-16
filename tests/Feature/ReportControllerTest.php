<?php

namespace Tests\Feature;

use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_reports_with_shop_and_date_filters(): void
    {
        $shopA = Shop::query()->create(['name' => 'Riyadh', 'slug' => 'riyadh', 'is_active' => true]);
        $shopB = Shop::query()->create(['name' => 'Jeddah', 'slug' => 'jeddah', 'is_active' => true]);
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $product = Product::query()->create([
            'name' => 'Dates',
            'sku' => 'DATES-1',
            'price' => 50,
            'cost_price' => 20,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        $saleA = Sale::query()->create([
            'shop_id' => $shopA->id,
            'cashier_id' => $admin->id,
            'idempotency_key' => 'rpt-a-1',
            'status' => SaleStatus::Synced,
            'sold_at' => now()->subDay(),
            'subtotal' => 100,
            'vat_total' => 15,
            'total' => 115,
            'payload_hash' => 'hash-a',
        ]);

        SaleItem::query()->create([
            'sale_id' => $saleA->id,
            'product_id' => $product->id,
            'product_name' => 'Dates',
            'sku' => 'DATES-1',
            'quantity' => 2,
            'unit_price' => 50,
            'unit_cost' => 20,
            'vat_rate' => 15,
            'vat_amount' => 15,
            'line_total' => 115,
        ]);

        Sale::query()->create([
            'shop_id' => $shopB->id,
            'cashier_id' => $admin->id,
            'idempotency_key' => 'rpt-b-1',
            'status' => SaleStatus::Synced,
            'sold_at' => now()->subDay(),
            'subtotal' => 200,
            'vat_total' => 30,
            'total' => 230,
            'payload_hash' => 'hash-b',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.index', [
                'shop_id' => $shopA->id,
                'start_date' => now()->subDays(2)->toDateString(),
                'end_date' => now()->toDateString(),
            ]));

        $response->assertOk();
        $response->assertSee('Reports');
        $response->assertSee('Riyadh</td>', false);
        $response->assertDontSee('Jeddah</td>', false);
        $response->assertSee('SAR 15.00');
    }

    public function test_shop_manager_is_forced_to_own_shop_scope_on_reports(): void
    {
        $managerShop = Shop::query()->create(['name' => 'Manager Shop', 'slug' => 'manager-shop', 'is_active' => true]);
        $otherShop = Shop::query()->create(['name' => 'Other Shop', 'slug' => 'other-shop', 'is_active' => true]);

        $manager = User::factory()->create([
            'role' => UserRole::ShopManager,
            'shop_id' => $managerShop->id,
        ]);

        Sale::query()->create([
            'shop_id' => $managerShop->id,
            'cashier_id' => $manager->id,
            'idempotency_key' => 'mgr-rpt-a',
            'status' => SaleStatus::Synced,
            'sold_at' => now(),
            'subtotal' => 100,
            'vat_total' => 15,
            'total' => 115,
            'payload_hash' => 'mgr-a',
        ]);

        Sale::query()->create([
            'shop_id' => $otherShop->id,
            'cashier_id' => $manager->id,
            'idempotency_key' => 'mgr-rpt-b',
            'status' => SaleStatus::Synced,
            'sold_at' => now(),
            'subtotal' => 200,
            'vat_total' => 30,
            'total' => 230,
            'payload_hash' => 'mgr-b',
        ]);

        $response = $this->actingAs($manager)
            ->get(route('reports.index', ['shop_id' => $otherShop->id]));

        $response->assertOk();
        $response->assertSee('Manager Shop</td>', false);
        $response->assertDontSee('Other Shop</td>', false);
    }

    public function test_cashier_cannot_access_reports(): void
    {
        $cashier = User::factory()->create(['role' => UserRole::Cashier]);

        $this->actingAs($cashier)
            ->get(route('reports.index'))
            ->assertForbidden();
    }
}
