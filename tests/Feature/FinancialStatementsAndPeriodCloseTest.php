<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SalePaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\Account;
use App\Models\Customer;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\Accounting\JournalEntryService;
use App\Services\Purchasing\PurchaseOrderReceivingService;
use App\Services\Purchasing\SupplierPaymentService;
use App\Services\Receivables\CustomerPaymentService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialStatementsAndPeriodCloseTest extends TestCase
{
    use RefreshDatabase;

    public function test_mixed_pos_ap_and_ar_transactions_remain_balanced_with_company_level_ap_ar_entries(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create([
            'name' => 'HQ Shop',
            'slug' => 'hq-shop',
            'is_active' => true,
        ]);

        $accountant = User::factory()->create(['role' => UserRole::Accountant]);
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'shop_id' => $shop->id]);

        $warehouse = Warehouse::query()->create(['name' => 'Main Warehouse', 'code' => 'WH-MAIN', 'is_active' => true]);
        $supplier = Supplier::query()->create(['name' => 'Supplier A', 'code' => 'SUP-A', 'payment_terms_days' => 30, 'is_active' => true]);

        $product = Product::query()->create([
            'name' => 'Rice 5KG',
            'sku' => 'RICE-5KG',
            'price' => 50,
            'cost_price' => 20,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        ShopStock::query()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 100,
        ]);

        $po = PurchaseOrder::query()->create([
            'po_number' => 'PO-900001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseOrderStatus::Submitted,
            'created_by' => $accountant->id,
        ]);

        $poItem = $po->items()->create([
            'product_id' => $product->id,
            'quantity_ordered' => 10,
            'unit_cost' => 20,
            'vat_rate' => 15,
        ]);

        $bill = app(PurchaseOrderReceivingService::class)->receive($po, [[
            'purchase_order_item_id' => $poItem->id,
            'quantity_received' => 10,
        ]], $accountant->id);

        app(SupplierPaymentService::class)->record(
            $bill->id,
            50,
            now()->toDateString(),
            'PAY-900001',
            null,
            $accountant->id
        );

        $customer = Customer::query()->create([
            'name' => 'Customer A',
            'code' => 'CUST-A',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $payload = [
            'sales' => [[
                'idempotency_key' => 'phase9-credit-sale-001',
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '100.00',
                'vat_total' => '15.00',
                'total' => '115.00',
                'payment_method' => SalePaymentMethod::CreditAccount->value,
                'customer_id' => $customer->id,
                'items' => [[
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => '2.000',
                    'unit_price' => '50.00',
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

        $sale = Sale::query()->where('idempotency_key', 'phase9-credit-sale-001')->firstOrFail();

        app(CustomerPaymentService::class)->record(
            $sale->id,
            60,
            now()->toDateString(),
            'CUSTPAY-900001',
            null,
            $accountant->id
        );

        $totals = JournalEntryLine::query()
            ->selectRaw('SUM(debit) as debit_sum, SUM(credit) as credit_sum')
            ->first();

        $this->assertNotNull($totals);
        $this->assertSame(
            number_format((float) $totals->debit_sum, 2, '.', ''),
            number_format((float) $totals->credit_sum, 2, '.', '')
        );

        $this->assertDatabaseHas('journal_entries', [
            'source' => 'supplier_bill',
            'shop_id' => null,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'source' => 'supplier_payment',
            'shop_id' => null,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'source' => 'customer_payment',
            'shop_id' => null,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'source' => 'pos_sale',
            'shop_id' => $shop->id,
        ]);

        $saleEntry = JournalEntry::query()->forAllShops()->where('sale_id', $sale->id)->firstOrFail();
        $cogsAccountId = (int) Account::query()->where('code', '5100')->value('id');
        $inventoryAccountId = (int) Account::query()->where('code', '1200')->value('id');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $saleEntry->id,
            'account_id' => $cogsAccountId,
            'debit' => '40.00',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $saleEntry->id,
            'account_id' => $inventoryAccountId,
            'debit' => '0.00',
            'credit' => '40.00',
        ]);
    }

    public function test_receiving_updates_weighted_average_cost(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create(['name' => 'HQ Shop', 'slug' => 'hq-shop', 'is_active' => true]);
        User::factory()->create(['role' => UserRole::Cashier, 'shop_id' => $shop->id]);
        $accountant = User::factory()->create(['role' => UserRole::Accountant]);

        $warehouse = Warehouse::query()->create(['name' => 'Cost Warehouse', 'code' => 'WH-COST', 'is_active' => true]);
        $supplier = Supplier::query()->create(['name' => 'Supplier B', 'code' => 'SUP-B', 'payment_terms_days' => 30, 'is_active' => true]);

        $product = Product::query()->create([
            'name' => 'Sugar 1KG',
            'sku' => 'SUGAR-1KG',
            'price' => 12,
            'cost_price' => 10,
            'average_cost' => 10,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        WarehouseStock::query()->create([
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $po = PurchaseOrder::query()->create([
            'po_number' => 'PO-900002',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseOrderStatus::Submitted,
            'created_by' => $accountant->id,
        ]);

        $poItem = $po->items()->create([
            'product_id' => $product->id,
            'quantity_ordered' => 5,
            'unit_cost' => 20,
            'vat_rate' => 15,
        ]);

        app(PurchaseOrderReceivingService::class)->receive($po, [[
            'purchase_order_item_id' => $poItem->id,
            'quantity_received' => 5,
        ]], $accountant->id);

        $product->refresh();

        $this->assertSame('13.3333', number_format((float) $product->average_cost, 4, '.', ''));
        $this->assertSame('13.33', number_format((float) $product->cost_price, 2, '.', ''));
    }

    public function test_journal_posting_is_blocked_for_closed_periods(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create(['name' => 'HQ Shop', 'slug' => 'hq-shop', 'is_active' => true]);
        $accountant = User::factory()->create(['role' => UserRole::Accountant]);

        FiscalPeriod::query()->create([
            'year' => (int) now()->year,
            'month' => (int) now()->month,
            'period_start' => now()->startOfMonth()->toDateString(),
            'period_end' => now()->endOfMonth()->toDateString(),
            'is_closed' => true,
            'closed_by' => $accountant->id,
            'closed_at' => now(),
        ]);

        $cashAccount = Account::query()->where('code', '1010')->firstOrFail();
        $equityAccount = Account::query()->where('code', '3100')->firstOrFail();

        $this->expectException(UnbalancedJournalEntryException::class);
        $this->expectExceptionMessage('Cannot post journal entry into a closed fiscal period.');

        app(JournalEntryService::class)->create([
            'shop_id' => $shop->id,
            'entry_date' => now()->toDateString(),
            'reference' => 'MANUAL-CLOSE-TEST',
            'description' => 'Manual posting in closed period',
            'source' => 'manual',
            'created_by' => $accountant->id,
        ], [
            [
                'account_id' => $cashAccount->id,
                'debit' => 100,
                'credit' => 0,
            ],
            [
                'account_id' => $equityAccount->id,
                'debit' => 0,
                'credit' => 100,
            ],
        ]);
    }
}
