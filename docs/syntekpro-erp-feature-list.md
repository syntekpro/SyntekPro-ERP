# SyntekPro ERP — Complete Feature List (Phase 0–9)

_As of the Phase 9 navigation fix, version 1.0.0_

## 1. Platform & Access

- Multi-tenant hub-and-spoke architecture: one central hub (`erp.syntekpro.com`) managing multiple shops, path-based shop POS terminals
- Four roles: `super_admin`, `shop_manager`, `cashier`, `accountant` — each with policy-enforced access boundaries, not just hidden UI
- Shop-scoped data isolation: shop managers/cashiers only ever see their own shop's data, enforced at the database query level via a global scope, not just the UI
- Docker-based deployment, self-hosted on your own VPS

## 2. Hub Management (Shops, Warehouses, Products, Users)

- **Shops** — create/edit/deactivate shop locations
- **Warehouses** — create/edit/deactivate central stock-holding locations
- **Products** — shared catalog across all shops, with VAT rate, cost price, moving average cost, and pricing
- **Users** — create/edit/deactivate hub and shop staff, with role assignment
- All four support deactivation (`is_active` flag) rather than hard deletion, preserving historical/audit integrity

## 3. Inventory & Stock

- Two-tier stock model: `warehouse_stock` (central) and `shop_stock` (per-shop) — a shop only sells from its own local stock, never a shared pool
- **Stock Transfers**: create a transfer from warehouse to shop, dispatch it, receive it — with row-level locking to prevent double-allocation of the same stock across concurrent transfers, and a stock-reservation check at transfer-creation time (not just at receiving)
- Weighted-average cost tracking per product, automatically recalculated on every purchase receipt

## 4. Point of Sale (POS)

- Offline-first Progressive Web App (installable, works without an internet connection)
- IndexedDB local queue + service worker for offline sales capture
- Idempotency-keyed sync — reconnecting after being offline never double-counts or loses a sale
- Three payment methods per sale: **cash**, **card**, **credit account** (bill-to-customer)
- Whole-sale rejection on sync conflicts — if any item in a queued sale has insufficient stock by the time it syncs, the entire sale is rejected for manual review rather than partially applied

## 5. Purchasing & Accounts Payable

- **Suppliers** — hub-level supplier records with configurable payment terms
- **Purchase Orders** — draft → submitted → partially received → received → closed lifecycle, targeting the central warehouse
- Partial receiving supported — receive less than the full ordered quantity, across multiple receipts against the same PO
- Receiving automatically generates a **Supplier Bill** and updates warehouse stock and product average cost, atomically
- **Supplier Payments** — record full or partial payments against a bill; overpayment is rejected
- **AP Aging report** — outstanding bills by supplier, bucketed by days overdue (current / 1–30 / 31–60 / 61–90 / 90+)

## 6. Sales & Accounts Receivable

- **Customers** — hub-level customer records with configurable payment terms and an optional credit limit
- Credit-account POS sales create an outstanding receivable against the customer, with a due date based on their payment terms
- Credit-limit enforcement — a new credit sale that would exceed a customer's limit is rejected, with proper locking to prevent two simultaneous sales both slipping through
- **Customer Payments** — record full or partial payments against an outstanding sale; overpayment is rejected
- **AR Aging report** — outstanding customer balances, same bucket structure as AP aging

## 7. Accounting — General Ledger

- Full double-entry Chart of Accounts (Assets, Liabilities, Equity, Revenue, Expenses), editable, seeded with a sensible Saudi-retail default set (see the User Guide for the full default list)
- Every journal entry is enforced to balance (total debits = total credits) before it can be saved — checked in integer cents to avoid floating-point rounding bugs
- **Auto-posting** — the ledger updates itself automatically from operational activity, with no manual bookkeeping required for standard transactions:
  - Every POS sale posts Revenue, VAT, and the correct cash/AR debit — automatically
  - Every POS sale also posts **Cost of Goods Sold** against Inventory, using the product's live average cost — automatically
  - Every supplier bill posts Inventory and Input VAT against Accounts Payable — automatically
  - Every supplier/customer payment posts the correct cash movement — automatically
- Manual journal entries are still available for accountant-entered adjustments
- **Trial Balance report** — confirms the whole ledger is internally consistent

## 8. Financial Statements & Period Close

- **Balance Sheet** — always company-wide, as of any date, with an explicit balanced/unbalanced check
- **Income Statement (P&L)** — viewable company-wide or filtered to a single shop, showing Revenue, COGS, Gross Profit, Operating Expenses, and Net Income
- **Cash Flow Statement** — indirect method, derived from the ledger rather than separately maintained
- **Fiscal Periods** — monthly periods with a close/reopen workflow; once a period is closed, no journal entry (manual or auto-posted) can be dated inside it, enforced at the accounting-engine level, not just the UI

## 9. Saudi Compliance

- 15% VAT handled throughout — POS sales, purchases, and reporting
- ZATCA Phase 1 QR code generation on every sale, using the real TLV (tag-length-value) encoding format
- Fields reserved for future ZATCA Phase 2 (XML/API e-invoicing) integration

## 10. Reporting

- VAT report, margin report, fast-moving SKU report (shop-filterable)
- Trial Balance, AP Aging, AR Aging, Balance Sheet, Income Statement, Cash Flow — all listed above, gathered here as the full reporting suite

## 11. Branding & Demo

- SyntekPro logo, favicon, and brand colors applied throughout the hub and POS
- PWA manifest with proper icon sizes for install-to-homescreen
- Demo environment infrastructure (`demo.erp.syntekpro.com`) — DNS and reverse proxy configured; seeded demo deployment itself still pending

---

## Known gaps (not yet built)

- Sales returns/refunds and purchase returns
- Stock takes / cycle counts
- Arabic/RTL localization
- ZATCA Phase 2 live e-invoicing submission
- Bank reconciliation, budgeting, multi-currency
- HR & payroll
- Granular (non-role-based) permissions, full audit-log trail
- Per-shop cost allocation for purchasing (currently company-level; see `docs/phase-9-financial-statements.md` for the reasoning)
