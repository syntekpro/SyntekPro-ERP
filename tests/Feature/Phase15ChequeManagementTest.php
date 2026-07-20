<?php

namespace Tests\Feature;

use App\Enums\SalePaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\SupplierBillStatus;
use App\Enums\UserRole;
use App\Exceptions\UnbalancedJournalEntryException;
use App\Models\Account;
use App\Models\Customer;
use App\Models\FiscalPeriod;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Cheques\ChequeService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase15ChequeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_recording_incoming_cheque_reduces_sale_outstanding_and_posts_to_pdc_receivable_not_cash(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$accountant, $sale] = $this->makeOpenCreditSale(500.00);

        $cheque = app(ChequeService::class)->recordIncomingForSale(
            $sale->id,
            200.00,
            'CHQ-AR-001',
            'Riyad Bank',
            now()->toDateString(),
            $accountant->id,
        );

        $sale->refresh();

        $this->assertSame('300.00', number_format((float) $sale->outstanding_balance, 2, '.', ''));
        $this->assertSame('pending', $cheque->status->value);

        $pdcReceivableId = (int) Account::query()->where('code', config('accounting.cheques.pdc_receivable_account_code'))->value('id');
        $accountsReceivableId = (int) Account::query()->where('code', config('accounting.receivables.accounts_receivable_account_code'))->value('id');
        $cashBankId = (int) Account::query()->where('code', config('accounting.receivables.payment_cash_or_bank_account_code'))->value('id');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $cheque->recorded_journal_entry_id,
            'account_id' => $pdcReceivableId,
            'debit' => '200.00',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $cheque->recorded_journal_entry_id,
            'account_id' => $accountsReceivableId,
            'debit' => '0.00',
            'credit' => '200.00',
        ]);

        $this->assertDatabaseMissing('journal_entry_lines', [
            'journal_entry_id' => $cheque->recorded_journal_entry_id,
            'account_id' => $cashBankId,
        ]);
    }

    public function test_clearing_incoming_cheque_moves_amount_from_pdc_receivable_to_cash(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$accountant, $sale] = $this->makeOpenCreditSale(350.00);

        $cheque = app(ChequeService::class)->recordIncomingForSale(
            $sale->id,
            150.00,
            'CHQ-AR-002',
            'Alinma Bank',
            now()->toDateString(),
            $accountant->id,
        );

        $cleared = app(ChequeService::class)->markCleared($cheque->id, $accountant->id, now()->toDateString());

        $this->assertSame('cleared', $cleared->status->value);
        $this->assertNotNull($cleared->cleared_journal_entry_id);

        $cashBankId = (int) Account::query()->where('code', config('accounting.receivables.payment_cash_or_bank_account_code'))->value('id');
        $pdcReceivableId = (int) Account::query()->where('code', config('accounting.cheques.pdc_receivable_account_code'))->value('id');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $cleared->cleared_journal_entry_id,
            'account_id' => $cashBankId,
            'debit' => '150.00',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $cleared->cleared_journal_entry_id,
            'account_id' => $pdcReceivableId,
            'debit' => '0.00',
            'credit' => '150.00',
        ]);
    }

    public function test_bouncing_pending_outgoing_cheque_restores_supplier_bill_balance_and_reverses_pdc_payable(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$accountant, $bill] = $this->makeOpenSupplierBill(400.00);

        $cheque = app(ChequeService::class)->recordOutgoingForSupplierBill(
            $bill->id,
            175.00,
            'CHQ-AP-001',
            'SNB',
            now()->toDateString(),
            $accountant->id,
        );

        $bill->refresh();
        $this->assertSame('225.00', number_format((float) $bill->outstanding_balance, 2, '.', ''));
        $this->assertSame(SupplierBillStatus::PartiallyPaid, $bill->status);

        $bounced = app(ChequeService::class)->markBounced($cheque->id, $accountant->id, now()->toDateString());

        $bill->refresh();
        $this->assertSame('400.00', number_format((float) $bill->outstanding_balance, 2, '.', ''));
        $this->assertSame(SupplierBillStatus::Open, $bill->status);
        $this->assertSame('bounced', $bounced->status->value);

        $pdcPayableId = (int) Account::query()->where('code', config('accounting.cheques.pdc_payable_account_code'))->value('id');
        $accountsPayableId = (int) Account::query()->where('code', config('accounting.purchasing.accounts_payable_account_code'))->value('id');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $bounced->bounced_journal_entry_id,
            'account_id' => $pdcPayableId,
            'debit' => '175.00',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $bounced->bounced_journal_entry_id,
            'account_id' => $accountsPayableId,
            'debit' => '0.00',
            'credit' => '175.00',
        ]);
    }

    public function test_cheque_cannot_be_cleared_twice(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$accountant, $sale] = $this->makeOpenCreditSale(260.00);

        $cheque = app(ChequeService::class)->recordIncomingForSale(
            $sale->id,
            100.00,
            'CHQ-AR-003',
            'Al Rajhi',
            now()->toDateString(),
            $accountant->id,
        );

        app(ChequeService::class)->markCleared($cheque->id, $accountant->id, now()->toDateString());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only pending cheques can be cleared.');

        app(ChequeService::class)->markCleared($cheque->id, $accountant->id, now()->toDateString());
    }

    public function test_cheque_cannot_be_bounced_after_being_cleared(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$accountant, $sale] = $this->makeOpenCreditSale(280.00);

        $cheque = app(ChequeService::class)->recordIncomingForSale(
            $sale->id,
            80.00,
            'CHQ-AR-004',
            'SABB',
            now()->toDateString(),
            $accountant->id,
        );

        app(ChequeService::class)->markCleared($cheque->id, $accountant->id, now()->toDateString());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Only pending cheques can be bounced.');

        app(ChequeService::class)->markBounced($cheque->id, $accountant->id, now()->toDateString());
    }

    public function test_cheque_action_inside_closed_fiscal_period_is_rejected(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$accountant, $sale] = $this->makeOpenCreditSale(500.00);

        $cheque = app(ChequeService::class)->recordIncomingForSale(
            $sale->id,
            120.00,
            'CHQ-AR-005',
            'ANB',
            now()->toDateString(),
            $accountant->id,
        );

        $today = now();

        FiscalPeriod::query()->create([
            'year' => (int) $today->year,
            'month' => (int) $today->month,
            'period_start' => $today->copy()->startOfMonth()->toDateString(),
            'period_end' => $today->copy()->endOfMonth()->toDateString(),
            'is_closed' => true,
            'closed_by' => $accountant->id,
            'closed_at' => now(),
        ]);

        try {
            app(ChequeService::class)->markCleared($cheque->id, $accountant->id, $today->toDateString());
            $this->fail('Expected closed fiscal period rejection was not thrown.');
        } catch (UnbalancedJournalEntryException $exception) {
            $this->assertStringContainsString('closed fiscal period', $exception->getMessage());
        }

        $this->assertSame('pending', $cheque->fresh()->status->value);
    }

    private function makeOpenCreditSale(float $outstanding): array
    {
        $shop = Shop::query()->create([
            'name' => 'Cheque AR Shop',
            'slug' => 'cheque-ar-shop',
            'is_active' => true,
        ]);

        $accountant = User::factory()->create([
            'role' => UserRole::Accountant,
            'shop_id' => null,
        ]);

        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
        ]);

        $customer = Customer::query()->create([
            'name' => 'Cheque AR Customer',
            'code' => 'CHQ-AR-CUST',
            'is_active' => true,
        ]);

        $sale = Sale::query()->create([
            'shop_id' => $shop->id,
            'cashier_id' => $cashier->id,
            'idempotency_key' => 'phase15-ar-'.str()->uuid()->toString(),
            'invoice_number' => 'INV-CHQ-'.random_int(1000, 9999),
            'status' => SaleStatus::Synced,
            'sold_at' => now(),
            'subtotal' => $outstanding,
            'vat_total' => 0,
            'excise_total' => 0,
            'total' => $outstanding,
            'payment_method' => SalePaymentMethod::CreditAccount,
            'customer_id' => $customer->id,
            'due_date' => now()->addDays(30)->toDateString(),
            'outstanding_balance' => $outstanding,
        ]);

        return [$accountant, $sale];
    }

    private function makeOpenSupplierBill(float $outstanding): array
    {
        $accountant = User::factory()->create([
            'role' => UserRole::Accountant,
        ]);

        $supplier = Supplier::query()->create([
            'name' => 'Cheque AP Supplier',
            'code' => 'CHQ-AP-SUP',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'name' => 'Cheque AP Warehouse',
            'code' => 'CHQ-AP-WH',
            'is_active' => true,
        ]);

        $bill = SupplierBill::query()->create([
            'bill_number' => 'BILL-CHQ-'.random_int(1000, 9999),
            'supplier_id' => $supplier->id,
            'purchase_order_id' => null,
            'warehouse_id' => $warehouse->id,
            'journal_entry_id' => null,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(20)->toDateString(),
            'subtotal' => $outstanding,
            'vat_total' => 0,
            'total' => $outstanding,
            'outstanding_balance' => $outstanding,
            'status' => SupplierBillStatus::Open,
            'created_by' => $accountant->id,
        ]);

        return [$accountant, $bill];
    }
}
