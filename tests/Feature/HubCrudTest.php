<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Products\FormPage as ProductFormPage;
use App\Livewire\Products\IndexPage as ProductIndexPage;
use App\Livewire\Shops\FormPage as ShopFormPage;
use App\Livewire\Shops\IndexPage as ShopIndexPage;
use App\Livewire\Warehouses\FormPage as WarehouseFormPage;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HubCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_dashboard_shows_real_counts(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
        ]);

        Shop::query()->create(['name' => 'Riyadh', 'slug' => 'riyadh', 'is_active' => true]);
        Shop::query()->create(['name' => 'Jeddah', 'slug' => 'jeddah', 'is_active' => true]);
        Warehouse::query()->create(['name' => 'Central', 'code' => 'WH-C', 'is_active' => true]);
        Product::query()->create(['name' => 'Scanner', 'sku' => 'SKU-1', 'price' => 12, 'vat_rate' => 15, 'is_active' => true]);
        Product::query()->create(['name' => 'Receipt Roll', 'sku' => 'SKU-2', 'price' => 8, 'vat_rate' => 15, 'is_active' => true]);
        Product::query()->create(['name' => 'Drawer', 'sku' => 'SKU-3', 'price' => 80, 'vat_rate' => 15, 'is_active' => true]);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSeeText('Shops')
            ->assertSeeText('Warehouses')
            ->assertSeeText('Products')
            ->assertSeeText('2')
            ->assertSeeText('1')
            ->assertSeeText('3');
    }

    public function test_super_admin_can_reach_phase_one_crud_pages(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $shop = Shop::query()->create(['name' => 'Riyadh', 'slug' => 'riyadh', 'is_active' => true]);
        $warehouse = Warehouse::query()->create(['name' => 'Central', 'code' => 'WH-C', 'is_active' => true]);
        $product = Product::query()->create(['name' => 'Scanner', 'sku' => 'SKU-1', 'price' => 12, 'vat_rate' => 15, 'is_active' => true]);

        $this->actingAs($admin)->get(route('shops.index'))->assertOk();
        $this->actingAs($admin)->get(route('shops.create'))->assertOk();
        $this->actingAs($admin)->get(route('shops.edit', $shop))->assertOk();

        $this->actingAs($admin)->get(route('warehouses.index'))->assertOk();
        $this->actingAs($admin)->get(route('warehouses.create'))->assertOk();
        $this->actingAs($admin)->get(route('warehouses.edit', $warehouse))->assertOk();

        $this->actingAs($admin)->get(route('products.index'))->assertOk();
        $this->actingAs($admin)->get(route('products.create'))->assertOk();
        $this->actingAs($admin)->get(route('products.edit', $product))->assertOk();
    }

    public function test_livewire_shop_crud_works(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(ShopFormPage::class)
            ->set('name', 'Dammam Branch')
            ->set('slug', 'dammam-branch')
            ->set('is_active', true)
            ->call('save')
            ->assertRedirect(route('shops.index'));

        $shop = Shop::query()->where('slug', 'dammam-branch')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ShopFormPage::class, ['shop' => $shop])
            ->set('name', 'Dammam Hub')
            ->call('save')
            ->assertRedirect(route('shops.index'));

        Livewire::actingAs($admin)
            ->test(ShopIndexPage::class)
            ->call('delete', $shop->id);

        $this->assertDatabaseHas('shops', [
            'id' => $shop->id,
            'is_active' => false,
        ]);
    }

    public function test_livewire_warehouse_crud_works(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(WarehouseFormPage::class)
            ->set('name', 'East Warehouse')
            ->set('code', 'WH-EAST')
            ->set('is_active', true)
            ->call('save')
            ->assertRedirect(route('warehouses.index'));

        $warehouse = Warehouse::query()->where('code', 'WH-EAST')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(WarehouseFormPage::class, ['warehouse' => $warehouse])
            ->set('name', 'East Regional Warehouse')
            ->call('save')
            ->assertRedirect(route('warehouses.index'));

        $this->assertDatabaseHas('warehouses', ['id' => $warehouse->id, 'name' => 'East Regional Warehouse']);
    }

    public function test_livewire_product_crud_works(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(ProductFormPage::class)
            ->set('name', 'Barcode Scanner')
            ->set('sku', 'BARCODE-SCANNER')
            ->set('barcode', '1234567890')
            ->set('price', '155.00')
            ->set('vat_rate', '15.00')
            ->set('is_active', true)
            ->call('save')
            ->assertRedirect(route('products.index'));

        $product = Product::query()->where('sku', 'BARCODE-SCANNER')->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ProductFormPage::class, ['product' => $product])
            ->set('price', '165.00')
            ->call('save')
            ->assertRedirect(route('products.index'));

        Livewire::actingAs($admin)
            ->test(ProductIndexPage::class)
            ->call('delete', $product->id);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }
}