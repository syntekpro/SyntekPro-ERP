# Phase 6: Chart of Accounts and General Ledger Core

## Scope

Phase 6 introduces the accounting ledger core only:

- Shared hub-level Chart of Accounts (COA)
- Shop-scoped journal entries and lines
- Balanced double-entry enforcement before persistence
- Synchronous POS auto-posting to GL in the same transaction as sale and stock movement
- Trial Balance report for ledger consistency checks

Excluded from this phase:

- Accounts Payable workflows
- Accounts Receivable workflows
- Financial statements (P and L, Balance Sheet)

## Role Decision

A dedicated accountant role was added.

Reasoning:

- Super admin remains the platform owner role with full access.
- Shop manager is an operational role tied to store-level operations.
- Cashier is POS-only.
- Accountant separates accounting duties from infrastructure duties and allows manual journal posting and trial-balance review without granting system-admin privileges.

Manual journal entry access is limited to super admin and accountant.

## Fiscal Year Assumption

Fiscal year is fixed as calendar year (January through December) in this phase and is not yet configurable.

See [config/accounting.php](../config/accounting.php).

## Seeded Default COA (Saudi Retail)

The default seeded structure is intentionally editable and code-driven (not ID-driven):

- 1000 Assets
- 1010 Cash on Hand
- 1020 Bank Account
- 1200 Inventory
- 1300 VAT Receivable
- 2000 Liabilities
- 2100 Accounts Payable Control
- 2200 VAT Payable
- 3000 Equity
- 3100 Owner Capital
- 3200 Retained Earnings
- 4000 Revenue
- 4100 Sales Revenue
- 5000 Expenses
- 5100 Cost of Goods Sold
- 5200 Rent Expense
- 5300 Utilities Expense
- 5400 Salaries Expense

Rationale:

- Includes explicit VAT Payable (liability) and VAT Receivable (asset) as required.
- Keeps retail essentials ready for POS and operating adjustments.
- Keeps the tree lightweight for Phase 6 while leaving room for deeper sub-accounts in later phases.

## POS Auto-Posting Journal Entry

POS sync now posts a journal entry in the same database transaction that writes the sale and decrements stock. If posting fails, the full transaction rolls back.

Configured account codes for POS posting:

- Debit account: 1010 Cash on Hand
- Credit account: 4100 Sales Revenue
- Credit account: 2200 VAT Payable

## Worked Example

Example POS sale:

- Subtotal: SAR 100.00
- VAT: SAR 15.00
- Total: SAR 115.00

Generated journal entry:

- Dr 1010 Cash on Hand: SAR 115.00
- Cr 4100 Sales Revenue: SAR 100.00
- Cr 2200 VAT Payable: SAR 15.00

Check:

- Total debits = SAR 115.00
- Total credits = SAR 115.00
- Entry is balanced.

## Additional Assumptions

- POS sales are treated as immediate cash receipts in Phase 6 (using configured cash account code).
- Account behavior is never tied to hardcoded numeric IDs; account codes are used for configurable mappings.
- Accounts with posted journal lines are deactivated instead of hard deleted.
