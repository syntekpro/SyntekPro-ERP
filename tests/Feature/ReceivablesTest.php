<?php

namespace Tests\Feature;

use App\Enums\SalePaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\UserRole;
use App\Models\Account;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\User;
use App\Services\Receivables\CustomerPaymentService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_sale_posts_accounts_receivable_and_sets_outstanding_balance(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$shop, $cashier, $product] = $this->bootstrapCashierContext();

        $customer = Customer::query()->create([
            'name' => 'Al Noor Trading',
            'code' => 'CUST-001',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $payload = [
            'sales' => [[
                'idempotency_key' => 'credit-sale-001',
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

        $sale = Sale::query()->where('idempotency_key', 'credit-sale-001')->firstOrFail();

        $this->assertSame(SalePaymentMethod::CreditAccount, $sale->payment_method);
        $this->assertSame($customer->id, $sale->customer_id);
        $this->assertSame('115.00', number_format((float) $sale->outstanding_balance, 2, '.', ''));
        $this->assertNotNull($sale->due_date);

        $journalEntry = JournalEntry::query()->forAllShops()->where('sale_id', $sale->id)->firstOrFail();

        $arAccountId = (int) Account::query()->where('code', '1100')->value('id');
        $salesRevenueAccountId = (int) Account::query()->where('code', '4100')->value('id');
        $vatPayableAccountId = (int) Account::query()->where('code', '2200')->value('id');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $journalEntry->id,
            'account_id' => $arAccountId,
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
    }

    public function test_payment_exceeding_credit_sale_outstanding_balance_is_rejected(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create(['name' => 'HQ Shop', 'slug' => 'hq-shop', 'is_active' => true]);
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'shop_id' => $shop->id]);
        $customer = Customer::query()->create(['name' => 'Apex Retail', 'code' => 'CUST-002', 'is_active' => true]);

        $sale = Sale::query()->create([
            'shop_id' => $shop->id,
            'cashier_id' => $cashier->id,
            'idempotency_key' => 'credit-sale-002',
            'status' => SaleStatus::Synced,
            'sold_at' => now(),
            'subtotal' => 100,
            'vat_total' => 15,
            'total' => 115,
            'payment_method' => SalePaymentMethod::CreditAccount,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(30)->toDateString(),
            'outstanding_balance' => 50,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Payment amount cannot exceed the outstanding bill balance.');

        app(CustomerPaymentService::class)->record(
            $sale->id,
            60,
            now()->toDateString(),
            'CUSTPAY-001',
            null,
            $cashier->id
        );
    }

    public function test_credit_sale_rejected_when_customer_credit_limit_would_be_exceeded(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$shop, $cashier, $product] = $this->bootstrapCashierContext();

        $customer = Customer::query()->create([
            'name' => 'Limit Test Customer',
            'code' => 'CUST-003',
            'payment_terms_days' => 30,
            'credit_limit' => 100,
            'is_active' => true,
        ]);

        Sale::query()->create([
            'shop_id' => $shop->id,
            'cashier_id' => $cashier->id,
            'idempotency_key' => 'existing-credit-sale',
            'status' => SaleStatus::Synced,
            'sold_at' => now()->subDays(2),
            'subtotal' => 90,
            'vat_total' => 0,
            'total' => 90,
            'payment_method' => SalePaymentMethod::CreditAccount,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(28)->toDateString(),
            'outstanding_balance' => 90,
        ]);

        $payload = [
            'sales' => [[
                'idempotency_key' => 'credit-sale-limit-001',
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '20.00',
                'vat_total' => '0.00',
                'total' => '20.00',
                'payment_method' => SalePaymentMethod::CreditAccount->value,
                'customer_id' => $customer->id,
                'items' => [[
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => '1.000',
                    'unit_price' => '20.00',
                    'vat_rate' => '0.00',
                    'vat_amount' => '0.00',
                    'line_total' => '20.00',
                ]],
            ]],
        ];

        $this->actingAs($cashier)
            ->postJson('/api/pos/sync', $payload)
            ->assertOk()
            ->assertJsonPath('results.0.status', SaleStatus::Rejected->value)
            ->assertJsonPath('results.0.message', 'Credit limit exceeded for this customer.');

        $this->assertDatabaseMissing('sales', [
            'idempotency_key' => 'credit-sale-limit-001',
        ]);
    }

    public function test_ar_aging_report_buckets_open_credit_sales_by_due_date(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create(['name' => 'HQ Shop', 'slug' => 'hq-shop', 'is_active' => true]);
        $accountant = User::factory()->create(['role' => UserRole::Accountant]);
        $cashier = User::factory()->create(['role' => UserRole::Cashier, 'shop_id' => $shop->id]);

        $customer = Customer::query()->create([
            'name' => 'Aging Customer',
            'code' => 'CUST-004',
            'is_active' => true,
        ]);

        $makeSale = function (string $key, int $daysOffset, float $amount) use ($shop, $cashier, $customer): void {
            Sale::query()->create([
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'idempotency_key' => $key,
                'status' => SaleStatus::Synced,
                'sold_at' => now()->subDays(max(-$daysOffset, 0)),
                'subtotal' => $amount,
                'vat_total' => 0,
                'total' => $amount,
                'payment_method' => SalePaymentMethod::CreditAccount,
                'customer_id' => $customer->id,
                'due_date' => now()->addDays($daysOffset)->toDateString(),
                'outstanding_balance' => $amount,
            ]);
        };

        $makeSale('sale-current', 2, 100);
        $makeSale('sale-1-30', -10, 200);
        $makeSale('sale-31-60', -40, 300);
        $makeSale('sale-61-90', -70, 400);
        $makeSale('sale-90-plus', -120, 500);

        $response = $this->actingAs($accountant)->get(route('reports.ar-aging'));

        $response->assertOk();
        $response->assertSee('Aging Customer');
        $response->assertSee('SAR 100.00');
        $response->assertSee('SAR 200.00');
        $response->assertSee('SAR 300.00');
        $response->assertSee('SAR 400.00');
        $response->assertSee('SAR 500.00');
    }

    public function test_cash_and_card_sales_still_post_debit_to_cash_account(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$shop, $cashier, $product] = $this->bootstrapCashierContext();

        foreach ([SalePaymentMethod::Cash->value, SalePaymentMethod::Card->value] as $index => $paymentMethod) {
            $payload = [
                'sales' => [[
                    'idempotency_key' => 'cash-card-sale-'.$index,
                    'shop_id' => $shop->id,
                    'cashier_id' => $cashier->id,
                    'sold_at' => now()->toISOString(),
                    'subtotal' => '50.00',
                    'vat_total' => '7.50',
                    'total' => '57.50',
                    'payment_method' => $paymentMethod,
                    'customer_id' => null,
                    'items' => [[
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'quantity' => '1.000',
                        'unit_price' => '50.00',
                        'vat_rate' => '15.00',
                        'vat_amount' => '7.50',
                        'line_total' => '57.50',
                    ]],
                ]],
            ];

            $this->actingAs($cashier)
                ->postJson('/api/pos/sync', $payload)
                ->assertOk()
                ->assertJsonPath('results.0.status', SaleStatus::Synced->value);

            $sale = Sale::query()->where('idempotency_key', 'cash-card-sale-'.$index)->firstOrFail();
            $entry = JournalEntry::query()->forAllShops()->where('sale_id', $sale->id)->firstOrFail();
            $cashAccountId = (int) Account::query()->where('code', '1010')->value('id');

            $this->assertDatabaseHas('journal_entry_lines', [
                'journal_entry_id' => $entry->id,
                'account_id' => $cashAccountId,
                'debit' => '57.50',
                'credit' => '0.00',
            ]);
        }
    }

    protected function bootstrapCashierContext(): array
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
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        ShopStock::query()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 100,
        ]);

        return [$shop, $cashier, $product];
    }
}
