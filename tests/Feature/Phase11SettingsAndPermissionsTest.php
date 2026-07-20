<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\StockTransferStatus;
use App\Enums\UserRole;
use App\Livewire\Settings\SettingsPage;
use App\Models\Account;
use App\Models\BusinessSetting;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\DebitNote;
use App\Models\DocumentCounter;
use App\Models\DocumentNumberFormat;
use App\Models\JournalEntry;
use App\Models\Permission;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\User;
use App\Models\UserPermission;
use App\Models\Warehouse;
use App\Services\Numbering\DocumentNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class Phase11SettingsAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_default_role_permissions_match_pre_phase_11_policy_behavior(): void
    {
        $fixtures = $this->policyFixtures();

        $expectations = [
            UserRole::SuperAdmin->value => [
                'account.viewAny' => true, 'account.view' => true, 'account.create' => true, 'account.update' => true, 'account.delete' => true,
                'customer.viewAny' => true, 'customer.view' => true, 'customer.create' => true, 'customer.update' => true,
                'creditNote.viewAny' => true, 'creditNote.view' => true, 'creditNote.create' => true,
                'debitNote.viewAny' => true, 'debitNote.view' => true, 'debitNote.create' => true,
                'journalEntry.viewAny' => true, 'journalEntry.view' => true, 'journalEntry.create' => true,
                'product.viewAny' => true, 'product.view' => true, 'product.create' => true, 'product.update' => true, 'product.delete' => true,
                'purchaseOrder.viewAny' => true, 'purchaseOrder.view' => true, 'purchaseOrder.create' => true, 'purchaseOrder.update' => true, 'purchaseOrder.submit' => true, 'purchaseOrder.receive' => true, 'purchaseOrder.close' => true,
                'shop.viewAny' => true, 'shop.view' => true, 'shop.create' => true, 'shop.update' => true, 'shop.delete' => true,
                'shopStock.viewAny' => true, 'shopStock.view' => true, 'shopStock.update' => true,
                'stockTransfer.viewAny' => true, 'stockTransfer.view' => true, 'stockTransfer.create' => true, 'stockTransfer.markInTransit' => true, 'stockTransfer.receive' => true,
                'supplierBill.viewAny' => true, 'supplierBill.view' => true, 'supplierBill.recordPayment' => true,
                'supplier.viewAny' => true, 'supplier.view' => true, 'supplier.create' => true, 'supplier.update' => true,
                'user.viewAny' => true, 'user.view' => true, 'user.create' => true, 'user.update' => true, 'user.delete' => true,
                'warehouse.viewAny' => true, 'warehouse.view' => true, 'warehouse.create' => true, 'warehouse.update' => true, 'warehouse.delete' => true,
            ],
            UserRole::Accountant->value => [
                'account.viewAny' => true, 'account.view' => true, 'account.create' => false, 'account.update' => false, 'account.delete' => false,
                'customer.viewAny' => true, 'customer.view' => true, 'customer.create' => true, 'customer.update' => true,
                'creditNote.viewAny' => true, 'creditNote.view' => true, 'creditNote.create' => true,
                'debitNote.viewAny' => true, 'debitNote.view' => true, 'debitNote.create' => true,
                'journalEntry.viewAny' => true, 'journalEntry.view' => true, 'journalEntry.create' => true,
                'product.viewAny' => true, 'product.view' => true, 'product.create' => false, 'product.update' => false, 'product.delete' => false,
                'purchaseOrder.viewAny' => true, 'purchaseOrder.view' => true, 'purchaseOrder.create' => true, 'purchaseOrder.update' => true, 'purchaseOrder.submit' => true, 'purchaseOrder.receive' => true, 'purchaseOrder.close' => true,
                'shop.viewAny' => false, 'shop.view' => false, 'shop.create' => false, 'shop.update' => false, 'shop.delete' => false,
                'shopStock.viewAny' => false, 'shopStock.view' => false, 'shopStock.update' => false,
                'stockTransfer.viewAny' => false, 'stockTransfer.view' => false, 'stockTransfer.create' => false, 'stockTransfer.markInTransit' => false, 'stockTransfer.receive' => false,
                'supplierBill.viewAny' => true, 'supplierBill.view' => true, 'supplierBill.recordPayment' => true,
                'supplier.viewAny' => true, 'supplier.view' => true, 'supplier.create' => true, 'supplier.update' => true,
                'user.viewAny' => false, 'user.view' => false, 'user.create' => false, 'user.update' => false, 'user.delete' => false,
                'warehouse.viewAny' => true, 'warehouse.view' => true, 'warehouse.create' => false, 'warehouse.update' => false, 'warehouse.delete' => false,
            ],
            UserRole::ShopManager->value => [
                'account.viewAny' => false, 'account.view' => false, 'account.create' => false, 'account.update' => false, 'account.delete' => false,
                'customer.viewAny' => false, 'customer.view' => false, 'customer.create' => false, 'customer.update' => false,
                'creditNote.viewAny' => false, 'creditNote.view' => false, 'creditNote.create' => false,
                'debitNote.viewAny' => false, 'debitNote.view' => false, 'debitNote.create' => false,
                'journalEntry.viewAny' => true, 'journalEntry.view' => true, 'journalEntry.create' => false,
                'product.viewAny' => true, 'product.view' => true, 'product.create' => false, 'product.update' => false, 'product.delete' => false,
                'purchaseOrder.viewAny' => false, 'purchaseOrder.view' => false, 'purchaseOrder.create' => false, 'purchaseOrder.update' => false, 'purchaseOrder.submit' => false, 'purchaseOrder.receive' => false, 'purchaseOrder.close' => false,
                'shop.viewAny' => false, 'shop.view' => true, 'shop.create' => false, 'shop.update' => false, 'shop.delete' => false,
                'shopStock.viewAny' => true, 'shopStock.view' => true, 'shopStock.update' => true,
                'stockTransfer.viewAny' => true, 'stockTransfer.view' => true, 'stockTransfer.create' => false, 'stockTransfer.markInTransit' => false, 'stockTransfer.receive' => true,
                'supplierBill.viewAny' => false, 'supplierBill.view' => false, 'supplierBill.recordPayment' => false,
                'supplier.viewAny' => false, 'supplier.view' => false, 'supplier.create' => false, 'supplier.update' => false,
                'user.viewAny' => false, 'user.view' => false, 'user.create' => false, 'user.update' => false, 'user.delete' => false,
                'warehouse.viewAny' => true, 'warehouse.view' => true, 'warehouse.create' => false, 'warehouse.update' => false, 'warehouse.delete' => false,
            ],
            UserRole::Cashier->value => [],
        ];

        foreach (UserRole::cases() as $role) {
            $user = User::factory()->create(['role' => $role, 'shop_id' => $fixtures['ownedShop']->id]);
            $expected = $expectations[$role->value] ?: array_fill_keys(array_keys($expectations[UserRole::SuperAdmin->value]), false);

            foreach ($expected as $case => $allowed) {
                [$ability, $target] = $this->gateCase($case, $fixtures);
                $this->assertSame($allowed, Gate::forUser($user)->allows($ability, $target), $role->value.' '.$case);
            }
        }
    }

    public function test_user_level_permission_override_grants_and_revokes_beyond_role_default(): void
    {
        $product = Product::query()->create(['name' => 'Override Item', 'sku' => 'OVERRIDE', 'price' => 10]);
        $cashier = User::factory()->create(['role' => UserRole::Cashier]);
        $manager = User::factory()->create(['role' => UserRole::ShopManager]);

        $updateProductPermission = Permission::query()->where('key', 'products.update')->firstOrFail();
        $viewProductPermission = Permission::query()->where('key', 'products.view')->firstOrFail();

        $this->assertFalse(Gate::forUser($cashier)->allows('update', $product));
        UserPermission::query()->create(['user_id' => $cashier->id, 'permission_id' => $updateProductPermission->id, 'effect' => 'grant']);
        $this->assertTrue(Gate::forUser($cashier)->allows('update', $product));

        $this->assertTrue(Gate::forUser($manager)->allows('view', $product));
        UserPermission::query()->create(['user_id' => $manager->id, 'permission_id' => $viewProductPermission->id, 'effect' => 'revoke']);
        $this->assertFalse(Gate::forUser($manager)->allows('view', $product));
    }

    public function test_document_numbering_respects_configured_custom_prefix(): void
    {
        DocumentNumberFormat::query()->where('key', 'sales')->update(['prefix' => 'SI-']);

        $this->assertSame('SI-000001', app(DocumentNumberService::class)->next('sales'));
        $this->assertDatabaseHas('document_counters', ['key' => 'sales', 'next_number' => 2]);
    }

    public function test_invalid_logo_path_falls_back_to_default_on_login_page(): void
    {
        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update([
            'logo_path' => 'branding/missing-logo.png',
            'favicon_path' => 'branding/missing-favicon.png',
        ]);

        $this->get('/login')
            ->assertOk()
            ->assertSee('/images/logo-full.png', false)
            ->assertSee('/images/icon-main.png', false)
            ->assertSee('Powered by');
    }

    public function test_dynamic_theme_css_overrides_compiled_tailwind_theme_variables(): void
    {
        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update([
            'theme' => 'red-sea',
        ]);

        $css = $this->get('/theme.css')->assertOk()->getContent();

        $this->assertStringContainsString('--brand-primary:#06b6d4', $css);
        $this->assertStringContainsString('--color-amber-400:#06b6d4', $css);
        $this->assertStringContainsString('--color-cyan-400:#f97316', $css);
        $this->assertStringContainsString('--color-stone-950:#082f49', $css);
        $this->assertStringContainsString('--color-slate-950:#082f49', $css);
    }

    public function test_settings_screen_updates_legal_name_and_vat_number(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(SettingsPage::class)
            ->set('settings.legal_name', 'Settings Screen LLC')
            ->set('settings.vat_number', '399999999900003')
            ->call('saveGeneral')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('business_settings', [
            'singleton_key' => 1,
            'legal_name' => 'Settings Screen LLC',
            'vat_number' => '399999999900003',
        ]);
    }

    protected function policyFixtures(): array
    {
        $ownedShop = Shop::query()->create(['name' => 'Owned', 'slug' => 'owned', 'is_active' => true]);
        $warehouse = Warehouse::query()->create(['name' => 'Central', 'code' => 'WH-C']);
        $product = Product::query()->create(['name' => 'Policy Item', 'sku' => 'POLICY', 'price' => 10]);
        $managedUser = User::factory()->create(['role' => UserRole::Cashier]);

        return [
            'ownedShop' => $ownedShop,
            'account' => Account::query()->create(['code' => '9999', 'name' => 'Policy Account', 'account_type' => 'asset']),
            'customer' => new Customer(),
            'creditNote' => new CreditNote(),
            'debitNote' => new DebitNote(),
            'journalEntry' => JournalEntry::query()->create(['shop_id' => $ownedShop->id, 'entry_date' => now()->toDateString(), 'source' => 'manual']),
            'product' => $product,
            'purchaseOrderDraft' => new PurchaseOrder(['status' => PurchaseOrderStatus::Draft]),
            'purchaseOrderSubmitted' => new PurchaseOrder(['status' => PurchaseOrderStatus::Submitted]),
            'purchaseOrderReceived' => new PurchaseOrder(['status' => PurchaseOrderStatus::Received]),
            'shop' => $ownedShop,
            'shopStock' => new ShopStock(['shop_id' => $ownedShop->id]),
            'stockTransferPending' => new StockTransfer(['destination_shop_id' => $ownedShop->id, 'status' => StockTransferStatus::Pending]),
            'stockTransferInTransit' => new StockTransfer(['destination_shop_id' => $ownedShop->id, 'status' => StockTransferStatus::InTransit]),
            'supplierBill' => new SupplierBill(['outstanding_balance' => 1]),
            'supplier' => new Supplier(),
            'managedUser' => $managedUser,
            'warehouse' => $warehouse,
        ];
    }

    protected function gateCase(string $case, array $fixtures): array
    {
        return match ($case) {
            'account.viewAny' => ['viewAny', Account::class], 'account.view' => ['view', $fixtures['account']], 'account.create' => ['create', Account::class], 'account.update' => ['update', $fixtures['account']], 'account.delete' => ['delete', $fixtures['account']],
            'customer.viewAny' => ['viewAny', Customer::class], 'customer.view' => ['view', $fixtures['customer']], 'customer.create' => ['create', Customer::class], 'customer.update' => ['update', $fixtures['customer']],
            'creditNote.viewAny' => ['viewAny', CreditNote::class], 'creditNote.view' => ['view', $fixtures['creditNote']], 'creditNote.create' => ['create', CreditNote::class],
            'debitNote.viewAny' => ['viewAny', DebitNote::class], 'debitNote.view' => ['view', $fixtures['debitNote']], 'debitNote.create' => ['create', DebitNote::class],
            'journalEntry.viewAny' => ['viewAny', JournalEntry::class], 'journalEntry.view' => ['view', $fixtures['journalEntry']], 'journalEntry.create' => ['create', JournalEntry::class],
            'product.viewAny' => ['viewAny', Product::class], 'product.view' => ['view', $fixtures['product']], 'product.create' => ['create', Product::class], 'product.update' => ['update', $fixtures['product']], 'product.delete' => ['delete', $fixtures['product']],
            'purchaseOrder.viewAny' => ['viewAny', PurchaseOrder::class], 'purchaseOrder.view' => ['view', $fixtures['purchaseOrderDraft']], 'purchaseOrder.create' => ['create', PurchaseOrder::class], 'purchaseOrder.update' => ['update', $fixtures['purchaseOrderDraft']], 'purchaseOrder.submit' => ['submit', $fixtures['purchaseOrderDraft']], 'purchaseOrder.receive' => ['receive', $fixtures['purchaseOrderSubmitted']], 'purchaseOrder.close' => ['close', $fixtures['purchaseOrderReceived']],
            'shop.viewAny' => ['viewAny', Shop::class], 'shop.view' => ['view', $fixtures['shop']], 'shop.create' => ['create', Shop::class], 'shop.update' => ['update', $fixtures['shop']], 'shop.delete' => ['delete', $fixtures['shop']],
            'shopStock.viewAny' => ['viewAny', ShopStock::class], 'shopStock.view' => ['view', $fixtures['shopStock']], 'shopStock.update' => ['update', $fixtures['shopStock']],
            'stockTransfer.viewAny' => ['viewAny', StockTransfer::class], 'stockTransfer.view' => ['view', $fixtures['stockTransferInTransit']], 'stockTransfer.create' => ['create', StockTransfer::class], 'stockTransfer.markInTransit' => ['markInTransit', $fixtures['stockTransferPending']], 'stockTransfer.receive' => ['receive', $fixtures['stockTransferInTransit']],
            'supplierBill.viewAny' => ['viewAny', SupplierBill::class], 'supplierBill.view' => ['view', $fixtures['supplierBill']], 'supplierBill.recordPayment' => ['recordPayment', $fixtures['supplierBill']],
            'supplier.viewAny' => ['viewAny', Supplier::class], 'supplier.view' => ['view', $fixtures['supplier']], 'supplier.create' => ['create', Supplier::class], 'supplier.update' => ['update', $fixtures['supplier']],
            'user.viewAny' => ['viewAny', User::class], 'user.view' => ['view', $fixtures['managedUser']], 'user.create' => ['create', User::class], 'user.update' => ['update', $fixtures['managedUser']], 'user.delete' => ['delete', $fixtures['managedUser']],
            'warehouse.viewAny' => ['viewAny', Warehouse::class], 'warehouse.view' => ['view', $fixtures['warehouse']], 'warehouse.create' => ['create', Warehouse::class], 'warehouse.update' => ['update', $fixtures['warehouse']], 'warehouse.delete' => ['delete', $fixtures['warehouse']],
        };
    }
}
