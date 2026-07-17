<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopStock;
use App\Models\User;
use App\Services\Accounting\JournalEntryService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingLedgerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unbalanced_journal_entry_is_rejected_before_persisting(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create([
            'name' => 'Riyadh Central',
            'slug' => 'riyadh-central',
            'is_active' => true,
        ]);

        $service = app(JournalEntryService::class);

        try {
            $service->create([
                'shop_id' => $shop->id,
                'entry_date' => now()->toDateString(),
                'reference' => 'ADJ-001',
                'description' => 'Invalid adjustment',
                'source' => 'manual',
            ], [
                ['account_id' => $this->accountId('1010'), 'debit' => '100.00', 'credit' => '0.00'],
                ['account_id' => $this->accountId('3100'), 'debit' => '0.00', 'credit' => '90.00'],
            ]);

            $this->fail('Expected UnbalancedJournalEntryException was not thrown.');
        } catch (UnbalancedJournalEntryException) {
            $this->assertDatabaseCount('journal_entries', 0);
            $this->assertDatabaseCount('journal_entry_lines', 0);
        }
    }

    public function test_trial_balance_nets_to_zero_for_mixed_transactions(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        $shop = Shop::query()->create([
            'name' => 'Jeddah Branch',
            'slug' => 'jeddah-branch',
            'is_active' => true,
        ]);

        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
        ]);

        $accountant = User::factory()->create([
            'role' => 'accountant',
            'shop_id' => null,
        ]);

        $product = Product::query()->create([
            'name' => 'Desk Printer',
            'sku' => 'PRN-001',
            'barcode' => '6280001000012',
            'price' => 100,
            'cost_price' => 60,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        ShopStock::query()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $payload = [
            'sales' => [[
                'idempotency_key' => 'trial-balance-sale-1',
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '100.00',
                'vat_total' => '15.00',
                'total' => '115.00',
                'items' => [[
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'barcode' => $product->barcode,
                    'quantity' => '1.000',
                    'unit_price' => '100.00',
                    'vat_rate' => '15.00',
                    'vat_amount' => '15.00',
                    'line_total' => '115.00',
                ]],
            ]],
        ];

        $this->actingAs($cashier)->postJson('/api/pos/sync', $payload)->assertOk();

        app(JournalEntryService::class)->create([
            'shop_id' => $shop->id,
            'entry_date' => now()->toDateString(),
            'reference' => 'ADJ-002',
            'description' => 'Rent paid in cash',
            'source' => 'manual',
            'created_by' => $accountant->id,
        ], [
            ['account_id' => $this->accountId('5200'), 'debit' => '50.00', 'credit' => '0.00', 'description' => 'Rent expense'],
            ['account_id' => $this->accountId('1010'), 'debit' => '0.00', 'credit' => '50.00', 'description' => 'Cash'],
        ]);

        $totals = JournalEntryLine::query()
            ->selectRaw('SUM(debit) as debit_sum, SUM(credit) as credit_sum')
            ->first();

        $this->assertNotNull($totals);
        $this->assertSame(
            number_format((float) $totals->debit_sum, 2, '.', ''),
            number_format((float) $totals->credit_sum, 2, '.', '')
        );

        $this->actingAs($accountant)
            ->get(route('reports.trial-balance', ['shop_id' => $shop->id]))
            ->assertOk()
            ->assertSee('Balanced');
    }

    protected function accountId(string $code): int
    {
        return (int) \App\Models\Account::query()->where('code', $code)->value('id');
    }
}
