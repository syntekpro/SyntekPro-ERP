<?php

namespace Tests\Feature;

use App\Enums\PurchaseOrderStatus;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\SupplierBill;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\Purchasing\PurchaseOrderReceivingService;
use App\Services\Purchasing\SupplierPaymentService;
use Database\Seeders\ChartOfAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasingAndApTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_receiving_updates_warehouse_stock_and_creates_bill_for_received_portion_only(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);
        Shop::query()->create(['name' => 'HQ Shop', 'slug' => 'hq-shop', 'is_active' => true]);

        $user = User::factory()->create(['role' => 'accountant']);

        $supplier = Supplier::query()->create([
            'name' => 'Riyadh Food Distributors',
            'code' => 'SUP-001',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create(['name' => 'Central Warehouse', 'code' => 'WH-CENTRAL', 'is_active' => true]);

        $product = Product::query()->create([
            'name' => 'Olive Oil 1L',
            'sku' => 'OO-1L',
            'price' => 40,
            'cost_price' => 25,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        $po = PurchaseOrder::query()->create([
            'po_number' => 'PO-000001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseOrderStatus::Submitted,
            'created_by' => $user->id,
        ]);

        $poItem = $po->items()->create([
            'product_id' => $product->id,
            'quantity_ordered' => 10,
            'unit_cost' => 20,
            'vat_rate' => 15,
        ]);

        $bill = app(PurchaseOrderReceivingService::class)->receive($po, [[
            'purchase_order_item_id' => $poItem->id,
            'quantity_received' => 4,
        ]], $user->id);

        $this->assertDatabaseHas('warehouse_stock', [
            'warehouse_id' => $warehouse->id,
            'product_id' => $product->id,
            'quantity' => 4,
        ]);

        $this->assertDatabaseHas('purchase_order_items', [
            'id' => $poItem->id,
            'quantity_received' => '4.000',
        ]);

        $this->assertDatabaseHas('supplier_bills', [
            'id' => $bill->id,
            'subtotal' => '80.00',
            'vat_total' => '12.00',
            'total' => '92.00',
            'outstanding_balance' => '92.00',
        ]);

        $this->assertDatabaseHas('supplier_bill_items', [
            'supplier_bill_id' => $bill->id,
            'product_id' => $product->id,
            'quantity' => '4.000',
            'net_amount' => '80.00',
            'vat_amount' => '12.00',
            'gross_amount' => '92.00',
        ]);

        $this->assertSame(PurchaseOrderStatus::PartiallyReceived->value, $po->fresh()->status->value);
    }

    public function test_payment_exceeding_remaining_balance_is_rejected(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);
        Shop::query()->create(['name' => 'HQ Shop', 'slug' => 'hq-shop', 'is_active' => true]);

        $user = User::factory()->create(['role' => 'accountant']);

        $supplier = Supplier::query()->create([
            'name' => 'Apex Supplies',
            'code' => 'SUP-002',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create(['name' => 'Main Warehouse', 'code' => 'WH-MAIN', 'is_active' => true]);

        $bill = SupplierBill::query()->create([
            'bill_number' => 'BILL-000001',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'bill_date' => now()->toDateString(),
            'due_date' => now()->addDays(30)->toDateString(),
            'subtotal' => 100,
            'vat_total' => 15,
            'total' => 115,
            'outstanding_balance' => 50,
            'status' => 'open',
            'created_by' => $user->id,
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Payment amount cannot exceed the outstanding bill balance.');

        app(SupplierPaymentService::class)->record(
            $bill->id,
            60,
            now()->toDateString(),
            'PAY-001',
            null,
            $user->id
        );
    }

    public function test_bill_and_payment_journal_entries_are_individually_balanced(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);
        Shop::query()->create(['name' => 'HQ Shop', 'slug' => 'hq-shop', 'is_active' => true]);

        $user = User::factory()->create(['role' => 'accountant']);

        $supplier = Supplier::query()->create([
            'name' => 'National Wholesalers',
            'code' => 'SUP-003',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create(['name' => 'Main Warehouse', 'code' => 'WH-MAIN', 'is_active' => true]);

        $product = Product::query()->create([
            'name' => 'Rice 5KG',
            'sku' => 'RICE-5KG',
            'price' => 45,
            'cost_price' => 30,
            'vat_rate' => 15,
            'is_active' => true,
        ]);

        $po = PurchaseOrder::query()->create([
            'po_number' => 'PO-000002',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseOrderStatus::Submitted,
            'created_by' => $user->id,
        ]);

        $poItem = $po->items()->create([
            'product_id' => $product->id,
            'quantity_ordered' => 5,
            'unit_cost' => 20,
            'vat_rate' => 15,
        ]);

        $bill = app(PurchaseOrderReceivingService::class)->receive($po, [[
            'purchase_order_item_id' => $poItem->id,
            'quantity_received' => 5,
        ]], $user->id);

        $billJournalEntryId = (int) $bill->journal_entry_id;
        $billTotals = JournalEntryLine::query()
            ->where('journal_entry_id', $billJournalEntryId)
            ->selectRaw('SUM(debit) as debit_sum, SUM(credit) as credit_sum')
            ->first();

        $this->assertNotNull($billTotals);
        $this->assertSame(
            number_format((float) $billTotals->debit_sum, 2, '.', ''),
            number_format((float) $billTotals->credit_sum, 2, '.', '')
        );

        $payment = app(SupplierPaymentService::class)->record(
            $bill->id,
            50,
            now()->toDateString(),
            'PAY-002',
            null,
            $user->id
        );

        $paymentTotals = JournalEntryLine::query()
            ->where('journal_entry_id', $payment->journal_entry_id)
            ->selectRaw('SUM(debit) as debit_sum, SUM(credit) as credit_sum')
            ->first();

        $this->assertNotNull($paymentTotals);
        $this->assertSame(
            number_format((float) $paymentTotals->debit_sum, 2, '.', ''),
            number_format((float) $paymentTotals->credit_sum, 2, '.', '')
        );
    }

    public function test_ap_aging_report_buckets_bills_by_overdue_days(): void
    {
        $this->seed(ChartOfAccountsSeeder::class);

        Shop::query()->create(['name' => 'HQ Shop', 'slug' => 'hq-shop', 'is_active' => true]);

        $accountant = User::factory()->create(['role' => 'accountant']);

        $supplier = Supplier::query()->create([
            'name' => 'Aging Test Supplier',
            'code' => 'SUP-004',
            'payment_terms_days' => 30,
            'is_active' => true,
        ]);

        $warehouse = Warehouse::query()->create(['name' => 'Aging Warehouse', 'code' => 'WH-AGING', 'is_active' => true]);

        $makeBill = function (string $billNumber, int $daysOffset, float $amount) use ($supplier, $warehouse, $accountant): void {
            SupplierBill::query()->create([
                'bill_number' => $billNumber,
                'supplier_id' => $supplier->id,
                'warehouse_id' => $warehouse->id,
                'bill_date' => now()->subDays(max($daysOffset, 0))->toDateString(),
                'due_date' => now()->addDays($daysOffset)->toDateString(),
                'subtotal' => $amount,
                'vat_total' => 0,
                'total' => $amount,
                'outstanding_balance' => $amount,
                'status' => 'open',
                'created_by' => $accountant->id,
            ]);
        };

        $makeBill('BILL-CURRENT', 3, 100);
        $makeBill('BILL-1-30', -10, 200);
        $makeBill('BILL-31-60', -40, 300);
        $makeBill('BILL-61-90', -70, 400);
        $makeBill('BILL-90+', -120, 500);

        $response = $this->actingAs($accountant)->get(route('reports.ap-aging'));

        $response->assertOk();
        $response->assertSee('Aging Test Supplier');
        $response->assertSee('SAR 100.00');
        $response->assertSee('SAR 200.00');
        $response->assertSee('SAR 300.00');
        $response->assertSee('SAR 400.00');
        $response->assertSee('SAR 500.00');
    }
}
