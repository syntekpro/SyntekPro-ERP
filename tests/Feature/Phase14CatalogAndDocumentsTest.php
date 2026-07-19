<?php

namespace Tests\Feature;

use App\Enums\SalePaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Livewire\Products\IndexPage as ProductIndexPage;
use App\Mail\DocumentLinkMail;
use App\Models\BusinessSetting;
use App\Models\Customer;
use App\Models\DocumentShare;
use App\Models\PriceCategory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Unit;
use App\Models\User;
use App\Services\Products\ProductCatalogSpreadsheetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class Phase14CatalogAndDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_importing_a_valid_file_creates_and_updates_products_with_unit_conversions_and_price_overrides(): void
    {
        $this->actingAsAdmin();
        $box = Unit::query()->create(['code' => 'BOX', 'name' => 'Box', 'is_active' => true]);
        $wholesale = PriceCategory::query()->create(['name' => 'Wholesale', 'is_active' => true]);
        $pcs = Unit::query()->where('code', 'PCS')->firstOrFail();
        $existing = Product::query()->create(['name' => 'Old Water', 'sku' => 'WATER-1', 'base_unit_id' => $pcs->id, 'price' => 10, 'cost_price' => 7, 'vat_rate' => 15, 'is_active' => true]);

        $csv = "SKU/code,name,description,base unit,price,purchase price,VAT rate,is_excise_applicable,excise_rate,is_active,stock_min,stock_max,Unit: BOX - factor,Price: Wholesale\n".
            "WATER-1,Bottled Water,Still water,PCS,12.50,8.00,15,0,,1,5,50,12,10.00\n".
            "JUICE-1,Orange Juice,Fresh,PCS,18.00,11.00,15,1,50,1,3,30,6,15.00\n";

        $preview = $this->previewCsv($csv);
        app(ProductCatalogSpreadsheetService::class)->commit($preview);

        $this->assertDatabaseHas('products', ['id' => $existing->id, 'name' => 'Bottled Water', 'price' => 12.50]);
        $this->assertDatabaseHas('products', ['sku' => 'JUICE-1', 'is_excise_applicable' => true]);
        $this->assertDatabaseHas('product_unit_conversions', ['unit_id' => $box->id, 'conversion_factor' => 12]);
        $this->assertDatabaseHas('product_prices', ['price_category_id' => $wholesale->id, 'price' => 10]);
    }

    public function test_importing_a_file_with_invalid_rows_commits_only_valid_rows_after_confirmation_and_reports_specific_errors(): void
    {
        $this->actingAsAdmin();
        Unit::query()->create(['code' => 'BOX', 'name' => 'Box', 'is_active' => true]);

        $csv = "SKU/code,name,description,base unit,price,purchase price,VAT rate,is_excise_applicable,excise_rate,is_active,stock_min,stock_max,Unit: BOX - factor\n".
            "VALID-1,Valid Product,,PCS,9.00,5.00,15,0,,1,,,12\n".
            "BAD-1,Bad Product,,DRM,-1,5.00,15,0,,1,,,12\n";

        $preview = $this->previewCsv($csv);

        $this->assertSame(1, $preview['created']);
        $this->assertSame(1, $preview['rejected']);
        $this->assertContains("row 3: unknown unit code 'DRM'", $preview['errors']);
        $this->assertContains('row 3: price must be a non-negative number', $preview['errors']);

        app(ProductCatalogSpreadsheetService::class)->commit($preview);

        $this->assertDatabaseHas('products', ['sku' => 'VALID-1']);
        $this->assertDatabaseMissing('products', ['sku' => 'BAD-1']);
    }

    public function test_export_then_reimport_of_same_catalog_produces_no_unintended_changes(): void
    {
        $this->actingAsAdmin();
        $box = Unit::query()->create(['code' => 'BOX', 'name' => 'Box', 'is_active' => true]);
        $wholesale = PriceCategory::query()->create(['name' => 'Wholesale', 'is_active' => true]);
        $product = Product::query()->create(['name' => 'Round Trip', 'sku' => 'ROUND-1', 'base_unit_id' => Unit::query()->where('code', 'PCS')->value('id'), 'price' => 20, 'cost_price' => 12, 'vat_rate' => 15, 'is_active' => true]);
        $product->unitConversions()->create(['unit_id' => $box->id, 'conversion_factor' => 24]);
        $product->prices()->create(['price_category_id' => $wholesale->id, 'price' => 17]);

        $catalog = app(ProductCatalogSpreadsheetService::class);
        $before = $catalog->exportCsv();
        $catalog->commit($catalog->preview($this->csvRows($before)));

        $this->assertSame($before, $catalog->exportCsv());
    }

    public function test_bulk_deactivate_from_products_list_deactivates_only_selected_rows(): void
    {
        $admin = $this->actingAsAdmin();
        $first = Product::query()->create(['name' => 'First', 'sku' => 'FIRST', 'price' => 1, 'vat_rate' => 15, 'is_active' => true]);
        $second = Product::query()->create(['name' => 'Second', 'sku' => 'SECOND', 'price' => 1, 'vat_rate' => 15, 'is_active' => true]);

        Livewire::actingAs($admin)
            ->test(ProductIndexPage::class)
            ->set('selectedProductIds', [$first->id])
            ->call('bulkSetActive', false);

        $this->assertDatabaseHas('products', ['id' => $first->id, 'is_active' => false]);
        $this->assertDatabaseHas('products', ['id' => $second->id, 'is_active' => true]);
    }

    public function test_generated_share_link_allows_unauthenticated_viewing_of_the_correct_single_document_only(): void
    {
        $sale = $this->saleDocument();
        $share = DocumentShare::query()->create(['document_type' => 'sale', 'document_id' => $sale->id, 'token' => 'token-ok', 'expires_at' => now()->addDays(30)]);

        $this->get(route('documents.shared', $share->token))
            ->assertOk()
            ->assertSee('INV-1')
            ->assertSee('Walk-in customer');
    }

    public function test_expired_or_revoked_share_link_is_rejected(): void
    {
        $sale = $this->saleDocument();
        DocumentShare::query()->create(['document_type' => 'sale', 'document_id' => $sale->id, 'token' => 'expired-token', 'expires_at' => now()->subDay()]);
        DocumentShare::query()->create(['document_type' => 'sale', 'document_id' => $sale->id, 'token' => 'revoked-token', 'expires_at' => now()->addDay(), 'revoked_at' => now()]);

        $this->get(route('documents.shared', 'expired-token'))->assertForbidden();
        $this->get(route('documents.shared', 'revoked-token'))->assertForbidden();
    }

    public function test_emailed_document_uses_configured_business_mail_from_name_and_address(): void
    {
        Mail::fake();
        $this->actingAsAdmin();
        $sale = $this->saleDocument();
        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update(['mail_from_name' => 'Syntek Accounts', 'mail_from_address' => 'accounts@example.test']);

        $this->post(route('documents.email', ['type' => 'sale', 'id' => $sale->id]), ['email' => 'customer@example.test'])->assertRedirect();

        Mail::assertSent(DocumentLinkMail::class, function (DocumentLinkMail $mail) {
            return $mail->fromAddress === 'accounts@example.test'
                && $mail->fromName === 'Syntek Accounts'
                && $mail->hasTo('customer@example.test');
        });
    }

    protected function previewCsv(string $csv): array
    {
        return app(ProductCatalogSpreadsheetService::class)->preview($this->csvRows($csv));
    }

    protected function csvRows(string $csv): array
    {
        $handle = fopen('php://temp', 'w+');
        fwrite($handle, $csv);
        rewind($handle);
        $rows = [];

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }

    protected function actingAsAdmin(): User
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($admin);

        return $admin;
    }

    protected function saleDocument(): Sale
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $shop = Shop::query()->create(['name' => 'Main Shop', 'slug' => 'main-shop', 'is_active' => true]);
        $product = Product::query()->create(['name' => 'Receipt Roll', 'sku' => 'ROLL', 'price' => 10, 'vat_rate' => 15, 'is_active' => true]);
        $sale = Sale::query()->create([
            'shop_id' => $shop->id,
            'cashier_id' => $admin->id,
            'idempotency_key' => 'sale-1',
            'invoice_number' => 'INV-1',
            'status' => SaleStatus::Synced,
            'sold_at' => now(),
            'subtotal' => 10,
            'vat_total' => 1.5,
            'excise_total' => 0,
            'total' => 11.5,
            'payment_method' => SalePaymentMethod::Cash,
            'outstanding_balance' => 0,
        ]);
        $sale->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 10,
            'unit_cost' => 6,
            'vat_rate' => 15,
            'vat_amount' => 1.5,
            'line_total' => 11.5,
        ]);

        return $sale;
    }
}
