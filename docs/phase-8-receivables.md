# Phase 8: Accounts Receivable

## Scope

Phase 8 adds customer and receivables flow with AR accounting integration:

- Hub-level customer master
- POS sale payment method support (`cash`, `card`, `credit_account`)
- Credit-account sale due dates and outstanding balances
- Customer payments with partial settlement support
- AR aging report by overdue buckets
- Concurrency-safe document numbering for purchase orders, supplier bills, and sales invoices

Out of scope in this phase:

- Financial statements (P and L, Balance Sheet)
- Shop-level allocation refinement for hub-level receivables posting (deferred to Phase 9)

## Hub-level vs Shop-scoped Design

Customers are hub-level.

Reasoning:

- The same customer can buy across multiple shop locations.
- Customer credit exposure and aging must be consolidated at company level.
- Duplicating customers per shop would fragment balances and distort AR reporting.

## Role Decision (Accountant vs Super Admin)

Customer, customer receivables, customer payments, and AR aging features are available to super admin and accountant.

Reasoning:

- Receivables and credit control are accounting operations.
- Accountant should operate AR workflows without super-admin escalation.
- Super admin retains full access as platform owner.

## POS Payment Method Behavior

`PostsSaleToLedger` now chooses debit account based on sale `payment_method`:

- `cash` or `card`: Debit Cash on Hand (existing behavior retained)
- `credit_account`: Debit Accounts Receivable and keep an outstanding balance on the sale

In all cases:

- Credit Sales Revenue
- Credit VAT Payable

## Credit Limit Decision

Implemented decision:

- `customers.credit_limit` is optional (`nullable`)
- `null` means no enforced limit
- if set, any new `credit_account` sale that would push total open outstanding above the limit is rejected

Reasoning:

- Optional limit supports both strict and flexible commercial terms.
- Enforcing against open outstanding balance is simple and auditable.
- The check is performed inside a transaction with row locking to reduce concurrency drift.

## Safe Numbering Decision

The earlier `max(id) + 1` pattern was replaced due to race-condition risk.

Implemented approach:

- shared `document_counters` table
- counter rows are locked with `lockForUpdate()`
- used for:
  - purchase order numbers (`PO-xxxxxx`)
  - supplier bill numbers (`BILL-xxxxxx`)
  - sales invoice numbers (`INV-xxxxxx`)

## Credit Sale Journal Entry

Configured account codes from `config/accounting.php`:

- Accounts Receivable: 1100 (default)
- Sales Revenue: 4100 (default)
- VAT Payable: 2200 (default)

### Worked Example: Credit Sale

Credit sale with subtotal SAR 1,000.00 and VAT SAR 150.00:

- Sale subtotal: SAR 1,000.00
- VAT total: SAR 150.00
- Sale total: SAR 1,150.00
- Outstanding balance created: SAR 1,150.00

Auto-posted entry:

- Dr 1100 Accounts Receivable: SAR 1,150.00
- Cr 4100 Sales Revenue: SAR 1,000.00
- Cr 2200 VAT Payable: SAR 150.00

Check:

- Total debits = SAR 1,150.00
- Total credits = SAR 1,150.00
- Entry is balanced.

## Customer Payment Journal Entry

Configured payment debit account:

- Payment cash or bank account: 1020 (default)

### Worked Example: Customer Payment

Customer pays SAR 400.00 against open AR:

- Dr 1020 Bank Account: SAR 400.00
- Cr 1100 Accounts Receivable: SAR 400.00

Check:

- Total debits = SAR 400.00
- Total credits = SAR 400.00
- Entry is balanced.

## AR Aging

Outstanding credit sales are bucketed by `due_date` versus today:

- Current: due today or in future
- 1-30: overdue by 1 to 30 days
- 31-60: overdue by 31 to 60 days
- 61-90: overdue by 61 to 90 days
- 90+: overdue by more than 90 days

## Shop Posting Caveat (Phase 7 Consistency)

Same caveat as Phase 7 remains in place:

- customers are hub-level but `journal_entries` remain shop-scoped
- customer payment entries therefore use a configured receivables posting shop ID (`accounting.receivables.posting_shop_id`), with fallback to first shop
- this is intentionally carried forward and deferred for full redesign in Phase 9
