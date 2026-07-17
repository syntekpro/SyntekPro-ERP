# Phase 7: Accounts Payable and Purchasing

## Scope

Phase 7 adds supplier and purchasing flow with AP accounting integration:

- Hub-level supplier master
- Warehouse-targeted purchase orders with partial receiving
- Auto-generated supplier bills on receipt
- Supplier payments with partial settlement support
- AP aging report by overdue buckets

Out of scope in this phase:

- Accounts Receivable
- Financial statements (P and L, Balance Sheet)

## Hub-level vs Shop-scoped Design

Suppliers and purchase orders are hub-level.

Reasoning:

- Suppliers are shared partners for the whole company, not a single shop.
- Purchase orders feed central warehouse stock.
- Shop allocation still goes through the existing stock-transfer workflow from warehouse to shop, keeping inventory movement audit paths consistent.

## Role Decision (Accountant vs Super Admin)

Supplier, PO, bill, payment, and AP aging features are available to super admin and accountant.

Reasoning:

- Purchasing and AP are accounting and procurement operations, not cashier or routine shop-manager tasks.
- Accountant should be able to operate these flows without requiring full super-admin privileges.
- Super admin still retains access as platform owner.

## Receiving and Billing Workflow

Receiving a PO (full or partial):

1. Locks PO and relevant warehouse_stock rows.
2. Increases warehouse_stock only for received quantities.
3. Updates purchase_order_items.quantity_received.
4. Auto-creates Supplier Bill for received portion only.
5. Auto-posts Supplier Bill journal entry through JournalEntryService.
6. Commits all steps in one transaction.

## Supplier Bill Journal Entry

Configured account codes from config/accounting.php:

- Inventory: 1200 (default)
- Input VAT Receivable: 1300 (default)
- Accounts Payable: 2100 (default)

### Worked Example: Bill on PO Receipt

Receive quantity with net cost SAR 1,000.00 and VAT SAR 150.00:

- Bill subtotal: SAR 1,000.00
- VAT total: SAR 150.00
- Bill total: SAR 1,150.00

Auto-posted entry:

- Dr 1200 Inventory: SAR 1,000.00
- Dr 1300 Input VAT Receivable: SAR 150.00
- Cr 2100 Accounts Payable: SAR 1,150.00

Check:

- Total debits = SAR 1,150.00
- Total credits = SAR 1,150.00
- Entry is balanced.

## Supplier Payment Journal Entry

Configured payment credit account:

- Payment cash or bank account: 1020 (default)

### Worked Example: Bill Payment

Pay SAR 400.00 against outstanding AP:

- Dr 2100 Accounts Payable: SAR 400.00
- Cr 1020 Bank Account: SAR 400.00

Check:

- Total debits = SAR 400.00
- Total credits = SAR 400.00
- Entry is balanced.

## AP Aging

Outstanding bills are bucketed by due_date versus today:

- Current: due today or in future
- 1-30: overdue by 1 to 30 days
- 31-60: overdue by 31 to 60 days
- 61-90: overdue by 61 to 90 days
- 90+: overdue by more than 90 days

## Additional Assumptions

- Purchasing entries post to a configured accounting posting shop ID because journal_entries are shop-scoped from Phase 6. If not configured, the first shop is used.
- Bill numbers and PO numbers are generated sequentially by current max ID pattern.
- Suppliers are deactivated rather than hard-deleted to preserve purchasing and AP history.
