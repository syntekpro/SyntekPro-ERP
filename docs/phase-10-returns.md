# Phase 10: Sales Returns and Purchase Returns

## Scope

Phase 10 adds operational and accounting support for:

- Sales returns through credit notes
- Purchase returns through debit notes
- Partial return quantities against original line items
- Reversal entries created as new journal entries only
- Closed-period enforcement for return posting dates

This phase does not edit, overwrite, or delete original journal entries. Corrections are always posted through new, separate reversal entries, consistent with the period-close design from Phase 9.

## Role Decision

Credit notes and debit notes are available to super admin and accountant.

Reasoning:

- Returns affect inventory, VAT, AR, AP, and profit.
- These are accounting-controlled workflows, not cashier or general shop-manager workflows.
- There is no separate approval status in this phase. Creation posts the return immediately, so the users who can create returns are also the users who effectively approve them.

## Reused Existing Patterns

This phase reuses existing infrastructure instead of reimplementing it:

- `JournalEntryService` for balanced-entry enforcement and closed-period rejection
- `DocumentNumberService` for concurrency-safe credit note and debit note numbering
- Row locking patterns already used in `PurchaseOrderReceivingService` and existing stock mutation flows

Implemented document number series:

- Credit notes: `CN-xxxxxx` using counter key `credit_note`
- Debit notes: `DN-xxxxxx` using counter key `debit_note`

## Credit Notes

### Operational Rules

- A credit note references one original sale.
- Each credit note line references one original `sale_item`.
- Partial returns are supported.
- A line cannot return more than:
  - originally sold quantity
  - minus quantity already returned on earlier credit notes for that same sale item

### Item Condition Behavior

Each credit note line has a `condition`:

- `sellable`: quantity is added back to `shop_stock` for the original sale shop under row lock
- `damaged`: quantity is not restocked; cost is posted to `config('accounting.returns.damaged_goods_account_code')`

### Journal Entry Design

The reversal entry uses the original sale economics, including the frozen `sale_items.unit_cost` captured when the sale posted.

Revenue and VAT reversal:

- Dr Sales Revenue
- Dr VAT Payable

Settlement side:

- cash or card original sale: Cr Cash/Bank refund account
- credit-account original sale: Cr Accounts Receivable first, then Cr Cash/Bank only for any excess beyond the sale's remaining outstanding balance

Cost side for `sellable` return lines:

- Dr Inventory
- Cr Cost of Goods Sold

Cost side for `damaged` return lines:

- Dr Inventory Write-off Expense
- Cr Cost of Goods Sold

Original journal entries remain untouched.

### Worked Example: Partial Sellable Return on Cash Sale

Original sale:

- 2 units sold
- Unit selling price: SAR 50.00
- VAT 15%
- Frozen unit cost on original sale item: SAR 20.00

Original sale totals:

- Subtotal: SAR 100.00
- VAT: SAR 15.00
- Total: SAR 115.00

Credit note for returning 1 unit in `sellable` condition:

- Return subtotal: SAR 50.00
- Return VAT: SAR 7.50
- Refund total: SAR 57.50
- Inventory restored at frozen cost: SAR 20.00

Credit note journal entry:

- Dr 4100 Sales Revenue: SAR 50.00
- Dr 2200 VAT Payable: SAR 7.50
- Cr 1020 Bank Account: SAR 57.50
- Dr 1200 Inventory: SAR 20.00
- Cr 5100 Cost of Goods Sold: SAR 20.00

Check:

- Total debits = SAR 77.50
- Total credits = SAR 77.50
- Entry is balanced.

Important detail:

- If product average cost later changes to SAR 99.00, this credit note still reverses inventory at SAR 20.00 because the source of truth is the original sale line's frozen `unit_cost`.

### Worked Example: Damaged Return

Using the same original sale and same 1-unit return, but `condition = damaged`:

- Return subtotal: SAR 50.00
- Return VAT: SAR 7.50
- Refund total: SAR 57.50
- Damaged goods cost: SAR 20.00

Credit note journal entry:

- Dr 4100 Sales Revenue: SAR 50.00
- Dr 2200 VAT Payable: SAR 7.50
- Cr 1020 Bank Account: SAR 57.50
- Dr 5500 Inventory Write-off Expense: SAR 20.00
- Cr 5100 Cost of Goods Sold: SAR 20.00

Check:

- Total debits = SAR 77.50
- Total credits = SAR 77.50
- Inventory is not restocked.

### Worked Example: Credit Sale Return With Remaining Balance

Original credit sale:

- Total sale: SAR 115.00
- Outstanding balance before return: SAR 115.00

Credit note total:

- SAR 57.50

Settlement result:

- Applied against original sale outstanding: SAR 57.50
- Cash refund: SAR 0.00
- Sale outstanding after note: SAR 57.50

Settlement journal effect:

- Cr 1100 Accounts Receivable: SAR 57.50
- no cash/bank credit line

### Worked Example: Overpaid Credit Sale Return

This is the main settlement edge case.

Original credit sale:

- Total sale: SAR 115.00

Customer payments already received:

- SAR 115.00

Outstanding balance before return:

- SAR 0.00

Credit note total:

- SAR 57.50

Settlement logic:

1. Apply the credit note against the referenced sale's remaining outstanding balance first.
2. Remaining outstanding is SAR 0.00, so nothing can be applied to AR.
3. The full SAR 57.50 becomes a cash/bank refund.

Result:

- Applied to sale balance: SAR 0.00
- Cash refund: SAR 57.50
- Sale outstanding after note: SAR 0.00

Journal settlement effect:

- no AR credit line
- Cr 1020 Bank Account: SAR 57.50

Mixed case example:

- Original sale total: SAR 115.00
- Customer already paid: SAR 90.00
- Remaining outstanding before return: SAR 25.00
- Credit note total: SAR 57.50

Then:

- first SAR 25.00 reduces the sale's outstanding balance to zero
- remaining SAR 32.50 is refunded in cash/bank

Settlement lines for that mixed case:

- Cr 1100 Accounts Receivable: SAR 25.00
- Cr 1020 Bank Account: SAR 32.50

## Debit Notes

### Operational Rules

- A debit note references one original supplier bill.
- Each debit note line references one original `supplier_bill_item`.
- Partial returns are supported.
- A line cannot return more than:
  - originally received quantity on that bill item
  - minus quantity already returned on earlier debit notes for that same bill item

### Warehouse Stock and Costing

- Warehouse stock is decreased under row lock when the debit note posts.
- The system does not recalculate or unwind product average cost on purchase returns.

Reasoning:

- Phase 9 uses a one-directional moving average during receipt.
- Reversing average cost on the way out would corrupt the cost basis of inventory received after the original receipt.
- The return should reverse inventory quantity and accounting value for the returned bill items, but not attempt to retroactively recompute later inventory layers.

### Journal Entry Design

Standard debit note reversal entry:

- Dr Accounts Payable
- Cr Inventory
- Cr Input VAT Receivable

The journal entry is still created through `JournalEntryService`, so balance and closed-period controls remain centralized.

### Worked Example: Debit Note Within Open Bill Balance

Original supplier bill from receipt of 5 units at SAR 20.00 plus 15% VAT:

- Bill subtotal: SAR 100.00
- Bill VAT: SAR 15.00
- Bill total: SAR 115.00
- Outstanding before return: SAR 115.00

Return 2 units:

- Return subtotal: SAR 40.00
- Return VAT: SAR 6.00
- Return total: SAR 46.00

Settlement result:

- Applied to supplier bill outstanding: SAR 46.00
- Bill outstanding after note: SAR 69.00

Debit note journal entry:

- Dr 2100 Accounts Payable: SAR 46.00
- Cr 1200 Inventory: SAR 40.00
- Cr 1300 VAT Receivable: SAR 6.00

Check:

- Total debits = SAR 46.00
- Total credits = SAR 46.00
- Entry is balanced.

### Worked Example: Debit Note Exceeding Bill Balance

This is the main AP edge case.

Starting position:

- Original supplier bill total: SAR 115.00
- Supplier payment already made: SAR 100.00
- Outstanding balance before return: SAR 15.00

Return 2 units:

- Return subtotal: SAR 40.00
- Return VAT: SAR 6.00
- Return total: SAR 46.00

Application logic inside the app:

1. Reduce the referenced bill's `outstanding_balance` first.
2. Cap that reduction at the bill's actual open balance.
3. Do not let the bill go negative.
4. Surface the extra amount explicitly for manual follow-up.

Result stored on the debit note:

- Applied to bill balance: SAR 15.00
- Excess requiring manual handling: SAR 31.00
- Bill outstanding after note: SAR 0.00

Journal entry still reflects the full supplier return economics while keeping AP tied to the bill's real open balance:

- Dr 2100 Accounts Payable: SAR 15.00
- Dr 1150 Due from Supplier - Returns: SAR 31.00
- Cr 1200 Inventory: SAR 40.00
- Cr 1300 VAT Receivable: SAR 6.00

Known limitation:

- The system does not yet implement a dedicated supplier credit-balance object or workflow.
- The excess SAR 31.00 is therefore surfaced on the debit note, posted to the due-from-supplier account, and shown in the UI as manual handling required.
- AP aging stays correct because the bill's open balance is capped at zero instead of becoming negative.

## Closed Period Enforcement

Return posting dates are blocked by the same Phase 9 fiscal-period control used for all other journal postings.

Behavior:

- If `note_date` falls inside a closed fiscal period, the return is rejected.
- Because return creation runs inside one transaction, stock mutations and balance adjustments roll back with the failed journal posting.

## Reports

### AR Aging

No separate AR-aging algorithm was needed.

Reasoning:

- credit notes reduce `sales.outstanding_balance`
- AR aging already buckets the remaining open balances from `sales`

Result:

- AR aging now reflects balances net of applied credit notes automatically through the existing report query.

### AP Aging

No separate AP-aging algorithm was needed.

Reasoning:

- debit notes reduce `supplier_bills.outstanding_balance`
- AP aging already buckets the remaining open balances from `supplier_bills`

Result:

- AP aging now reflects balances net of applied debit notes automatically through the existing report query.

### Income Statement

No return-specific income statement code was required.

Reasoning:

- the income statement is ledger-derived from journal lines by account type
- credit notes debit Sales Revenue, which naturally reduces net revenue
- sellable and damaged return cost reversals credit COGS, while damaged returns also debit the write-off expense account

Result:

- returns now flow into net revenue and expense totals automatically through the existing ledger-driven statement logic from Phase 9
- this phase confirmed that the behavior was already automatic once the correct reversal entries were posted

## Verification Summary

Implemented feature coverage includes:

- partial sellable return restocks correctly and posts a balanced reversal entry using the original frozen sale-item cost
- damaged return does not restock and posts to write-off expense instead of inventory
- cash sale credit note creates a refund
- open credit sale credit note reduces AR first with no cash movement when fully covered by outstanding balance
- fully paid credit sale credit note refunds cash for the full returned amount
- debit note reduces supplier bill outstanding correctly
- debit note excess over bill balance is capped on the bill and flagged for manual handling
- closed-period return posting is rejected and rolled back
- original journal entries remain unchanged after returns; only new journal entries are added