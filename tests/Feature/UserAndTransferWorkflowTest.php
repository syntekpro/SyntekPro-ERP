<?php

namespace Tests\Feature;

use App\Enums\StockTransferStatus;
use App\Enums\UserRole;
use App\Livewire\StockTransfers\FormPage as StockTransferFormPage;
use App\Livewire\StockTransfers\IndexPage as StockTransferIndexPage;
use App\Livewire\Users\FormPage as UserFormPage;
use App\Livewire\Users\IndexPage as UserIndexPage;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UserAndTransferWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_reach_user_and_transfer_pages(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)->get(route('users.index'))->assertOk();
        $this->actingAs($admin)->get(route('users.create'))->assertOk();
        $this->actingAs($admin)->get(route('stock-transfers.index'))->assertOk();
        $this->actingAs($admin)->get(route('stock-transfers.create'))->assertOk();
    }

    public function test_livewire_user_crud_supports_role_and_shop_assignment(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $shop = Shop::query()->create(['name' => 'Riyadh', 'slug' => 'riyadh', 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(UserFormPage::class)
            ->set('name', 'Riyadh Manager')
            ->set('email', 'manager@example.com')
            ->set('role', UserRole::ShopManager->value)
            ->set('shop_id', $shop->id)
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('save')
            ->assertRedirect(route('users.index'));

        $managedUser = User::query()->where('email', 'manager@example.com')->firstOrFail();

        $this->assertSame(UserRole::ShopManager, $managedUser->role);
        $this->assertSame($shop->id, $managedUser->shop_id);

        Livewire::actingAs($admin)
            ->test(UserFormPage::class, ['user' => $managedUser])
            ->set('role', UserRole::Cashier->value)
            ->call('save')
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', [
            'id' => $managedUser->id,
            'role' => UserRole::Cashier->value,
        ]);

        Livewire::actingAs($admin)
            ->test(UserIndexPage::class)
            ->call('delete', $managedUser->id);

        $this->assertDatabaseHas('users', [
            'id' => $managedUser->id,
            'is_active' => false,
        ]);
    }

    public function test_transfer_workflow_creates_dispatches_and_receives_stock(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $shop = Shop::query()->create(['name' => 'Khobar', 'slug' => 'khobar', 'is_active' => true]);
        $manager = User::factory()->create(['role' => UserRole::ShopManager, 'shop_id' => $shop->id]);
        $warehouse = Warehouse::query()->create(['name' => 'Central', 'code' => 'WH-C', 'is_active' => true]);
        $product = Product::query()->create(['name' => 'Scanner', 'sku' => 'SKU-T1', 'price' => 100, 'vat_rate' => 15, 'is_active' => true]);

        WarehouseStock::query()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 25,
        ]);

        Livewire::actingAs($admin)
            ->test(StockTransferFormPage::class)
            ->set('source_warehouse_id', $warehouse->id)
            ->set('destination_shop_id', $shop->id)
            ->set('notes', 'Initial transfer')
            ->set('items.0.product_id', $product->id)
            ->set('items.0.quantity', '5.000')
            ->call('save')
            ->assertRedirect(route('stock-transfers.index'));

        $transfer = StockTransfer::query()->with('items')->firstOrFail();

        $this->assertSame(StockTransferStatus::Pending, $transfer->status);

        Livewire::actingAs($admin)
            ->test(StockTransferIndexPage::class)
            ->call('markInTransit', $transfer->id);

        $transfer->refresh();

        $this->assertSame(StockTransferStatus::InTransit, $transfer->status);

        Livewire::actingAs($manager)
            ->test(StockTransferIndexPage::class)
            ->call('receive', $transfer->id);

        $transfer->refresh();

        $this->assertSame(StockTransferStatus::Received, $transfer->status);
        $this->assertSame($manager->id, $transfer->received_by);

        $this->assertDatabaseHas('warehouse_stock', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
        ]);

        $this->assertDatabaseHas('shop_stock', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
        ]);

        $warehouseStock = WarehouseStock::query()->where('warehouse_id', $warehouse->id)->where('product_id', $product->id)->firstOrFail();
        $shopStock = ShopStock::query()->forAllShops()->where('shop_id', $shop->id)->where('product_id', $product->id)->firstOrFail();

        $this->assertSame('20.000', $warehouseStock->quantity);
        $this->assertSame('5.000', $shopStock->quantity);
    }
}