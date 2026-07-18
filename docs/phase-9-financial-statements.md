# Phase 9: Financial Statements and Period Close

## What Was Implemented

- Weighted-average inventory costing:
  - Added `products.average_cost`.
  - Purchase receiving recalculates weighted average on each receipt.
  - POS sale line cost now pulls from product average cost.
- POS journal posting now records COGS in the same entry:
  - Dr COGS
  - Cr Inventory
- Company-level accounting entries for AP/AR operational flows:
  - `supplier_bill`, `supplier_payment`, and `customer_payment` post with `shop_id = null`.
  - POS entries stay at shop level.
- Period close controls:
  - Added `fiscal_periods` table (monthly granularity).
  - Added close/reopen UI and routes.
  - Journal posting is blocked if entry date is in a closed period.
- Financial statements:
  - Balance Sheet (company-wide)
  - Income Statement (company-wide or by shop)
  - Cash Flow (indirect, company-wide)

## Why Monthly Fiscal Periods

Monthly granularity is used because:

- It aligns with VAT filing and management reporting cadence.
- It gives enough control for locking historical postings without excessive operational overhead.
- It supports year-end controls while still allowing month-level reopen/adjust workflows.

## Worked Journal Example

Example sequence:

1. Receive inventory from supplier (10 units @ 20, VAT 15%):
   - Net 200, VAT 30, Total 230
2. Sell 2 units on credit account (subtotal 100, VAT 15, total 115), product average cost = 20:
   - COGS = 40
3. Customer pays 60 on account
4. Supplier paid 50

### Entry A: Supplier Bill

- Dr Inventory 200
- Dr Input VAT Receivable 30
- Cr Accounts Payable 230

### Entry B: POS Credit Sale

- Dr Accounts Receivable 115
- Cr Sales Revenue 100
- Cr VAT Payable 15
- Dr COGS 40
- Cr Inventory 40

### Entry C: Customer Payment

- Dr Cash/Bank 60
- Cr Accounts Receivable 60

### Entry D: Supplier Payment

- Dr Accounts Payable 50
- Cr Cash/Bank 50

### Control Totals

- Total debits: 200 + 30 + 115 + 40 + 60 + 50 = 495
- Total credits: 230 + 100 + 15 + 40 + 60 + 50 = 495
- Net: 0

This is the accounting basis for statement integrity and trial balance zero difference.
