<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Enums\SalePaymentMethod;
use App\Enums\SaleStatus;
use App\Enums\SupplierBillStatus;
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
use App\Models\SupplierBill;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\Purchasing\PurchaseOrderReceivingService;
use App\Services\Purchasing\SupplierPaymentService;
use App\Services\Receivables\CustomerPaymentService;
use App\Services\Returns\CreditNoteService;
use App\Services\Returns\DebitNoteService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReturnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_sellable_credit_note_restocks_and_posts_new_balanced_reversal_entry(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$sale, $cashier, $shop, $product] = $this->createSaleContext('return-sale-001', SalePaymentMethod::Cash);
        $saleItem = $sale->items->sole();
        $originalEntry = JournalEntry::query()->forAllShops()->where('sale_id', $sale->id)->firstOrFail();
        $originalLines = JournalEntryLine::query()
            ->where('journal_entry_id', $originalEntry->id)
            ->orderBy('id')
            ->get(['account_id', 'debit', 'credit', 'description'])
            ->toArray();

        $product->update([
            'average_cost' => 99,
            'cost_price' => 99,
        ]);

        $creditNote = app(CreditNoteService::class)->record(
            $sale->id,
            now()->toDateString(),
            [[
                'sale_item_id' => $saleItem->id,
                'quantity' => 1,
                'condition' => 'sellable',
            ]],
            'Partial sellable return',
            $cashier->id,
        );

        $this->assertDatabaseHas('shop_stock', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => '9.000',
        ]);

        $this->assertSame('50.00', number_format((float) $creditNote->subtotal, 2, '.', ''));
        $this->assertSame('7.50', number_format((float) $creditNote->vat_total, 2, '.', ''));
        $this->assertSame('57.50', number_format((float) $creditNote->total, 2, '.', ''));
        $this->assertSame('57.50', number_format((float) $creditNote->refund_amount, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $creditNote->applied_to_sale_balance, 2, '.', ''));

        $reversalEntry = $creditNote->journalEntry()->firstOrFail();
        $totals = JournalEntryLine::query()
            ->where('journal_entry_id', $reversalEntry->id)
            ->selectRaw('SUM(debit) as debit_sum, SUM(credit) as credit_sum')
            ->first();

        $this->assertNotNull($totals);
        $this->assertSame(
            number_format((float) $totals->debit_sum, 2, '.', ''),
            number_format((float) $totals->credit_sum, 2, '.', '')
        );

        $salesRevenueAccountId = (int) Account::query()->where('code', '4100')->value('id');
        $vatPayableAccountId = (int) Account::query()->where('code', '2200')->value('id');
        $refundAccountId = (int) Account::query()->where('code', '1020')->value('id');
        $inventoryAccountId = (int) Account::query()->where('code', '1200')->value('id');
        $cogsAccountId = (int) Account::query()->where('code', '5100')->value('id');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $reversalEntry->id,
            'account_id' => $salesRevenueAccountId,
            'debit' => '50.00',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $reversalEntry->id,
            'account_id' => $vatPayableAccountId,
            'debit' => '7.50',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $reversalEntry->id,
            'account_id' => $refundAccountId,
            'debit' => '0.00',
            'credit' => '57.50',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $reversalEntry->id,
            'account_id' => $inventoryAccountId,
            'debit' => '20.00',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $reversalEntry->id,
            'account_id' => $cogsAccountId,
            'debit' => '0.00',
            'credit' => '20.00',
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'id' => $originalEntry->id,
            'source' => 'pos_sale',
        ]);

        $this->assertSame($originalLines, JournalEntryLine::query()
            ->where('journal_entry_id', $originalEntry->id)
            ->orderBy('id')
            ->get(['account_id', 'debit', 'credit', 'description'])
            ->toArray());

        $this->assertSame(2, JournalEntry::query()->forAllShops()->whereIn('source', ['pos_sale', 'credit_note'])->count());
    }

    public function test_damaged_credit_note_does_not_restock_and_posts_to_writeoff_account(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$sale, $cashier, $shop, $product] = $this->createSaleContext('return-sale-damaged', SalePaymentMethod::Cash);
        $saleItem = $sale->items->sole();

        $creditNote = app(CreditNoteService::class)->record(
            $sale->id,
            now()->toDateString(),
            [[
                'sale_item_id' => $saleItem->id,
                'quantity' => 1,
                'condition' => 'damaged',
            ]],
            'Damaged return',
            $cashier->id,
        );

        $this->assertDatabaseHas('shop_stock', [
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => '8.000',
        ]);

        $reversalEntry = $creditNote->journalEntry()->firstOrFail();
        $writeoffAccountId = (int) Account::query()->where('code', '5500')->value('id');
        $inventoryAccountId = (int) Account::query()->where('code', '1200')->value('id');
        $cogsAccountId = (int) Account::query()->where('code', '5100')->value('id');

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $reversalEntry->id,
            'account_id' => $writeoffAccountId,
            'debit' => '20.00',
            'credit' => '0.00',
        ]);

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $reversalEntry->id,
            'account_id' => $cogsAccountId,
            'debit' => '0.00',
            'credit' => '20.00',
        ]);

        $this->assertSame(0, JournalEntryLine::query()
            ->where('journal_entry_id', $reversalEntry->id)
            ->where('account_id', $inventoryAccountId)
            ->count());
    }

    public function test_credit_note_against_cash_sale_generates_cash_refund(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$sale, $cashier] = $this->createSaleContext('return-sale-cash-refund', SalePaymentMethod::Cash);
        $saleItem = $sale->items->sole();

        $creditNote = app(CreditNoteService::class)->record(
            $sale->id,
            now()->toDateString(),
            [[
                'sale_item_id' => $saleItem->id,
                'quantity' => 1,
                'condition' => 'sellable',
            ]],
            null,
            $cashier->id,
        );

        $refundAccountId = (int) Account::query()->where('code', '1020')->value('id');

        $this->assertSame('57.50', number_format((float) $creditNote->refund_amount, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $creditNote->applied_to_sale_balance, 2, '.', ''));
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $creditNote->journal_entry_id,
            'account_id' => $refundAccountId,
            'debit' => '0.00',
            'credit' => '57.50',
        ]);
    }

    public function test_credit_note_against_credit_sale_with_remaining_balance_reduces_balance_without_cash_movement(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$sale, $cashier] = $this->createSaleContext('return-sale-credit-open', SalePaymentMethod::CreditAccount);
        $saleItem = $sale->items->sole();

        $creditNote = app(CreditNoteService::class)->record(
            $sale->id,
            now()->toDateString(),
            [[
                'sale_item_id' => $saleItem->id,
                'quantity' => 1,
                'condition' => 'sellable',
            ]],
            null,
            $cashier->id,
        );

        $arAccountId = (int) Account::query()->where('code', '1100')->value('id');
        $refundAccountId = (int) Account::query()->where('code', '1020')->value('id');

        $this->assertSame('57.50', number_format((float) $sale->fresh()->outstanding_balance, 2, '.', ''));
        $this->assertSame('57.50', number_format((float) $creditNote->applied_to_sale_balance, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $creditNote->refund_amount, 2, '.', ''));

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $creditNote->journal_entry_id,
            'account_id' => $arAccountId,
            'debit' => '0.00',
            'credit' => '57.50',
        ]);

        $this->assertSame(0, JournalEntryLine::query()
            ->where('journal_entry_id', $creditNote->journal_entry_id)
            ->where('account_id', $refundAccountId)
            ->count());
    }

    public function test_credit_note_against_fully_paid_credit_sale_triggers_cash_refund_for_full_amount(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$sale, $cashier] = $this->createSaleContext('return-sale-credit-paid', SalePaymentMethod::CreditAccount);
        $saleItem = $sale->items->sole();

        app(CustomerPaymentService::class)->record(
            $sale->id,
            115,
            now()->toDateString(),
            'PAY-CREDIT-SALE-FULL',
            null,
            $cashier->id,
        );

        $creditNote = app(CreditNoteService::class)->record(
            $sale->id,
            now()->toDateString(),
            [[
                'sale_item_id' => $saleItem->id,
                'quantity' => 1,
                'condition' => 'sellable',
            ]],
            null,
            $cashier->id,
        );

        $arAccountId = (int) Account::query()->where('code', '1100')->value('id');
        $refundAccountId = (int) Account::query()->where('code', '1020')->value('id');

        $this->assertSame('0.00', number_format((float) $sale->fresh()->outstanding_balance, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $creditNote->applied_to_sale_balance, 2, '.', ''));
        $this->assertSame('57.50', number_format((float) $creditNote->refund_amount, 2, '.', ''));

        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $creditNote->journal_entry_id,
            'account_id' => $refundAccountId,
            'debit' => '0.00',
            'credit' => '57.50',
        ]);

        $this->assertSame(0, JournalEntryLine::query()
            ->where('journal_entry_id', $creditNote->journal_entry_id)
            ->where('account_id', $arAccountId)
            ->count());
    }

    public function test_debit_note_reduces_supplier_bill_outstanding_balance_correctly(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$bill, $accountant, $warehouse, $product] = $this->createSupplierBillContext('BILL-RET-001');
        $billItem = $bill->items->sole();

        $debitNote = app(DebitNoteService::class)->record(
            $bill->id,
            now()->toDateString(),
            [[
                'supplier_bill_item_id' => $billItem->id,
                'quantity' => 2,
            ]],
            'Return two units',
            $accountant->id,
        );

        $this->assertSame('69.00', number_format((float) $bill->fresh()->outstanding_balance, 2, '.', ''));
        $this->assertSame(SupplierBillStatus::PartiallyPaid, $bill->fresh()->status);
        $this->assertSame('46.00', number_format((float) $debitNote->applied_to_bill_balance, 2, '.', ''));
        $this->assertSame('0.00', number_format((float) $debitNote->excess_amount, 2, '.', ''));
        $this->assertDatabaseHas('warehouse_stock', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => '3.000',
        ]);
    }

    public function test_debit_note_that_exceeds_bill_balance_is_capped_and_flagged_for_manual_handling(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$bill, $accountant] = $this->createSupplierBillContext('BILL-RET-EXCESS');
        $billItem = $bill->items->sole();

        app(SupplierPaymentService::class)->record(
            $bill->id,
            100,
            now()->toDateString(),
            'SUPPAY-RET-EXCESS',
            null,
            $accountant->id,
        );

        $debitNote = app(DebitNoteService::class)->record(
            $bill->id,
            now()->toDateString(),
            [[
                'supplier_bill_item_id' => $billItem->id,
                'quantity' => 2,
            ]],
            'Return exceeds open AP',
            $accountant->id,
        );

        $apAccountId = (int) Account::query()->where('code', '2100')->value('id');
        $dueFromSupplierAccountId = (int) Account::query()->where('code', '1150')->value('id');

        $this->assertSame('0.00', number_format((float) $bill->fresh()->outstanding_balance, 2, '.', ''));
        $this->assertSame(SupplierBillStatus::Paid, $bill->fresh()->status);
        $this->assertSame('15.00', number_format((float) $debitNote->applied_to_bill_balance, 2, '.', ''));
        $this->assertSame('31.00', number_format((float) $debitNote->excess_amount, 2, '.', ''));
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $debitNote->journal_entry_id,
            'account_id' => $apAccountId,
            'debit' => '15.00',
            'credit' => '0.00',
        ]);
        $this->assertDatabaseHas('journal_entry_lines', [
            'journal_entry_id' => $debitNote->journal_entry_id,
            'account_id' => $dueFromSupplierAccountId,
            'debit' => '31.00',
            'credit' => '0.00',
        ]);
    }

    public function test_open_supplier_bill_total_reconciles_to_ap_ledger_balance_after_excess_debit_note(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$billA, $accountant] = $this->createSupplierBillContext('BILL-RET-RECON-A');
        [$billB] = $this->createSupplierBillContext('BILL-RET-RECON-B');
        [$billC] = $this->createSupplierBillContext('BILL-RET-RECON-C');

        app(SupplierPaymentService::class)->record(
            $billA->id,
            40,
            now()->toDateString(),
            'SUPPAY-RECON-A',
            null,
            $accountant->id,
        );

        app(SupplierPaymentService::class)->record(
            $billB->id,
            100,
            now()->toDateString(),
            'SUPPAY-RECON-B',
            null,
            $accountant->id,
        );

        app(DebitNoteService::class)->record(
            $billB->id,
            now()->toDateString(),
            [[
                'supplier_bill_item_id' => $billB->items()->sole()->id,
                'quantity' => 2,
            ]],
            'Excess supplier return',
            $accountant->id,
        );

        app(DebitNoteService::class)->record(
            $billC->id,
            now()->toDateString(),
            [[
                'supplier_bill_item_id' => $billC->items()->sole()->id,
                'quantity' => 1,
            ]],
            'Standard supplier return',
            $accountant->id,
        );

        $openBillsTotal = round((float) SupplierBill::query()->sum('outstanding_balance'), 2);
        $accountsPayableAccountId = (int) Account::query()->where('code', '2100')->value('id');

        $apLedgerRow = JournalEntryLine::query()
            ->where('account_id', $accountsPayableAccountId)
            ->selectRaw('COALESCE(SUM(credit), 0) as credit_sum, COALESCE(SUM(debit), 0) as debit_sum')
            ->first();

        $apLedgerBalance = round((float) $apLedgerRow->credit_sum - (float) $apLedgerRow->debit_sum, 2);

        $this->assertSame('167.00', number_format($openBillsTotal, 2, '.', ''));
        $this->assertSame(
            number_format($openBillsTotal, 2, '.', ''),
            number_format($apLedgerBalance, 2, '.', '')
        );
    }

    public function test_return_dated_inside_closed_fiscal_period_is_rejected_and_rolled_back(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$sale, $cashier, $shop, $product] = $this->createSaleContext('return-sale-closed-period', SalePaymentMethod::Cash);
        $saleItem = $sale->items->sole();
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

        $this->expectException(UnbalancedJournalEntryException::class);
        $this->expectExceptionMessage('Cannot post journal entry into a closed fiscal period.');

        try {
            app(CreditNoteService::class)->record(
                $sale->id,
                now()->toDateString(),
                [[
                    'sale_item_id' => $saleItem->id,
                    'quantity' => 1,
                    'condition' => 'sellable',
                ]],
                null,
                $cashier->id,
            );
        } finally {
            $this->assertDatabaseCount('credit_notes', 0);
            $this->assertDatabaseHas('shop_stock', [
                'shop_id' => $shop->id,
                'product_id' => $product->id,
                'quantity' => '8.000',
            ]);
        }
    }

    public function test_returns_do_not_modify_or_delete_original_journal_entries_only_new_entries_are_created(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        [$sale, $cashier] = $this->createSaleContext('return-sale-regression', SalePaymentMethod::Cash);
        [$bill, $accountant] = $this->createSupplierBillContext('BILL-RET-REG');

        $saleEntry = JournalEntry::query()->forAllShops()->where('sale_id', $sale->id)->firstOrFail();
        $saleLines = JournalEntryLine::query()
            ->where('journal_entry_id', $saleEntry->id)
            ->orderBy('id')
            ->get(['account_id', 'debit', 'credit', 'description'])
            ->toArray();

        $billEntry = JournalEntry::query()->forAllShops()->whereKey($bill->journal_entry_id)->firstOrFail();
        $billLines = JournalEntryLine::query()
            ->where('journal_entry_id', $billEntry->id)
            ->orderBy('id')
            ->get(['account_id', 'debit', 'credit', 'description'])
            ->toArray();

        app(CreditNoteService::class)->record(
            $sale->id,
            now()->toDateString(),
            [[
                'sale_item_id' => $sale->items->sole()->id,
                'quantity' => 1,
                'condition' => 'sellable',
            ]],
            null,
            $cashier->id,
        );

        app(DebitNoteService::class)->record(
            $bill->id,
            now()->toDateString(),
            [[
                'supplier_bill_item_id' => $bill->items->sole()->id,
                'quantity' => 1,
            ]],
            null,
            $accountant->id,
        );

        $this->assertDatabaseHas('journal_entries', ['id' => $saleEntry->id, 'source' => 'pos_sale']);
        $this->assertDatabaseHas('journal_entries', ['id' => $billEntry->id, 'source' => 'supplier_bill']);

        $this->assertSame($saleLines, JournalEntryLine::query()
            ->where('journal_entry_id', $saleEntry->id)
            ->orderBy('id')
            ->get(['account_id', 'debit', 'credit', 'description'])
            ->toArray());

        $this->assertSame($billLines, JournalEntryLine::query()
            ->where('journal_entry_id', $billEntry->id)
            ->orderBy('id')
            ->get(['account_id', 'debit', 'credit', 'description'])
            ->toArray());

        $this->assertSame(4, JournalEntry::query()->forAllShops()->whereIn('source', ['pos_sale', 'supplier_bill', 'credit_note', 'debit_note'])->count());
    }

    protected function createSaleContext(string $idempotencyKey, SalePaymentMethod $paymentMethod): array
    {
        $shop = Shop::query()->create([
            'name' => 'Return Shop',
            'slug' => 'return-shop',
            'is_active' => true,
        ]);

        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
        ]);

        $product = Product::query()->create([
            'name' => 'Returnable Item',
            'sku' => 'RET-001',
            'barcode' => '6290000000001',
            'price' => 50,
            'cost_price' => 20,
            'average_cost' => 20,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        ShopStock::query()->create([
            'shop_id' => $shop->id,
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $customerId = null;

        if ($paymentMethod === SalePaymentMethod::CreditAccount) {
            $customerId = Customer::query()->create([
                'name' => 'Return Customer',
                'code' => 'RET-CUST-001',
                'payment_terms_days' => 30,
                'is_active' => true,
            ])->id;
        }

        $payload = [
            'sales' => [[
                'idempotency_key' => $idempotencyKey,
                'shop_id' => $shop->id,
                'cashier_id' => $cashier->id,
                'sold_at' => now()->toISOString(),
                'subtotal' => '100.00',
                'vat_total' => '15.00',
                'total' => '115.00',
                'payment_method' => $paymentMethod->value,
                'customer_id' => $customerId,
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

        $sale = Sale::query()->where('idempotency_key', $idempotencyKey)->with('items')->firstOrFail();

        return [$sale, $cashier, $shop, $product];
    }

    protected function createSupplierBillContext(string $billNumberSeed): array
    {
        Shop::query()->firstOrCreate(
            ['slug' => 'hq-shop'],
            ['name' => 'HQ Shop', 'is_active' => true]
        );

        $accountant = User::factory()->create(['role' => UserRole::Accountant]);
        $supplier = Supplier::query()->create([
            'name' => 'Return Supplier '.$billNumberSeed,
            'code' => 'SUP-'.$billNumberSeed,
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create([
            'name' => 'Return Warehouse '.$billNumberSeed,
            'code' => 'WH-'.$billNumberSeed,
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'name' => 'Supplier Return Item '.$billNumberSeed,
            'sku' => 'SUP-RET-'.$billNumberSeed,
            'price' => 40,
            'cost_price' => 20,
            'average_cost' => 20,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        $purchaseOrder = PurchaseOrder::query()->create([
            'po_number' => 'PO-'.$billNumberSeed,
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseOrderStatus::Submitted,
            'created_by' => $accountant->id,
        ]);

        $purchaseOrderItem = $purchaseOrder->items()->create([
            'product_id' => $product->id,
            'quantity_ordered' => 5,
            'unit_cost' => 20,
            'vat_rate' => 15,
        ]);

        $bill = app(PurchaseOrderReceivingService::class)->receive($purchaseOrder, [[
            'purchase_order_item_id' => $purchaseOrderItem->id,
            'quantity_received' => 5,
        ]], $accountant->id);

        return [$bill, $accountant, $warehouse, $product];
    }
}