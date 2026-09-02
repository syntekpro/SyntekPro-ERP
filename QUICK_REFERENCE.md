# 🎯 SyntekPro ERP - Quick Reference & Visual Guide

> Visual diagrams, flowcharts, and quick-reference tables for common operations across SyntekPro ERP

---

## 📋 Quick Navigation Map

```text
┌──────────────────────────────────────────────────────────────────────────────────┐
│                             BACK OFFICE DASHBOARD                                │
│        (First page after login - Real-time metrics, status pills, Quick Menu)    │
└───────┬──────────────────────┬──────────────────────┬────────────────────┬───────┘
        │                      │                      │                    │
┌───────┴──────┐       ┌───────┴──────┐       ┌───────┴──────┐     ┌───────┴──────┐
│  OPERATIONS  │       │  PURCHASING  │       │    SALES     │     │  ACCOUNTING  │
├──────────────┤       ├──────────────┤       ├──────────────┤     ├──────────────┤
│ • Shops      │       │ • Suppliers  │       │ • Customers  │     │ • Accounts GL│
│ • Warehouses │       │ • Purchase   │       │ • Receivables│     │ • Journal Ent│
│ • Products   │       │   Orders     │       │ • Credit     │     │ • Cheques    │
│ • Transfers  │       │ • Bills (AP) │       │   Notes      │     │ • Fiscal Per │
│ • CSV Import │       │ • Debit Notes│       │ • POS Cashier│     │ • Bank Accs  │
│   & Export   │       │              │       │              │     │ • Bank Recon │
└───────┬──────┘       └───────┬──────┘       └───────┬──────┘     └───────┬──────┘
        │                      │                      │                    │
        └──────────────────────┼──────────────────────┴────────────────────┘
                               │
                ┌──────────────┴──────────────┐
                │                             │
        ┌───────┴──────┐              ┌───────┴──────┐
        │   REPORTS    │              │ADMINISTRATION│
        ├──────────────┤              ├──────────────┤
        │ • Overview   │              │ • Users      │
        │ • Trial Bal  │              │ • Units      │
        │ • Balance Sh │              │ • Price Cats │
        │ • Income Stmt│              │ • Categories │
        │ • Cash Flow  │              │ • Brands     │
        │ • AP Aging   │              │ • Settings & │
        │ • AR Aging   │              │   Branding   │
        │              │              │ • ZATCA Cert │
        └──────────────┘              └──────────────┘
```

---

## 📊 Module Quick Links

| Module | Navigation Path | Route Name | Purpose |
|--------|-----------------|------------|---------|
| **Dashboard** | Home | `dashboard` | Executive KPIs, charts & quick-action FAB |
| **Shops** | Operations → Shops | `shops.index` | Multi-branch retail locations |
| **Warehouses** | Operations → Warehouses | `warehouses.index` | Central stock-holding hubs |
| **Products** | Operations → Products | `products.index` | Central master catalog, barcodes & pricing |
| **Product Import/Export** | Operations → Products → Import / Export | `products.import` / `products.export` | Bulk CSV transfer with column mapping preview |
| **Stock Transfers** | Operations → Stock Transfers | `stock-transfers.index` | Inter-branch dispatch, transit & receipt |
| **Suppliers** | Purchasing → Suppliers | `suppliers.index` | Vendor directory, contacts & terms |
| **Purchase Orders** | Purchasing → Purchase Orders | `purchase-orders.index` | Procurement lifecycle & receiving |
| **Supplier Bills** | Purchasing → Supplier Bills | `supplier-bills.index` | AP invoices generated from PO receipts |
| **Debit Notes** | Purchasing → Debit Notes | `debit-notes.index` | Vendor returns & AP debit memos |
| **Customers** | Sales → Customers | `customers.index` | Client master records & credit limits |
| **Customer Receivables** | Sales → Customer Receivables | `customer-receivables.index` | AR tracking, invoice balances & payments |
| **Credit Notes** | Sales → Credit Notes | `credit-notes.index` | Customer sales returns & refunds |
| **POS** | Sales → POS | `pos.sales` | Offline-first PWA cashier checkout terminal |
| **Chart of Accounts** | Accounting → Accounts | `accounts.index` | Standard double-entry account hierarchy |
| **Journal Entries** | Accounting → Journal Entries | `journal-entries.index` | Manual adjustments with auto-balance check |
| **Cheques Register** | Accounting → Cheques Register | `cheques.index` | PDC register (Received/Issued, Clear, Bounce) |
| **Fiscal Periods** | Accounting → Fiscal Periods | `fiscal-periods.index` | Monthly financial period lock & reopen |
| **Bank Accounts** | Accounting → Bank Accounts | `bank-accounts.index` | Bank accounts & treasury books |
| **Bank Reconciliation** | Accounting → Bank Reconciliation | `bank-reconciliation.index` | Statement CSV upload & transaction matching |
| **Reports Overview** | Reports → Reports Overview | `reports.index` | Summary analytics, sales, tax & inventory |
| **Trial Balance** | Reports → Trial Balance | `reports.trial-balance` | Ledger debits & credits equality report |
| **Balance Sheet** | Reports → Balance Sheet | `reports.balance-sheet` | Company financial position (Assets/Liab/Equity)|
| **Income Statement** | Reports → Income Statement | `reports.income-statement` | P&L (Company or Shop-filtered) |
| **Cash Flow Statement** | Reports → Cash Flow Statement | `reports.cash-flow` | Indirect operating, investing & financing flow |
| **AP Aging** | Reports → AP Aging | `reports.ap-aging` | Payables overdue buckets (Current, 1-30, 31-60+)|
| **AR Aging** | Reports → AR Aging | `reports.ar-aging` | Receivables aging buckets |
| **Users** | Administration → Users | `users.index` | Staff accounts, shop assignment & role controls|
| **Units** | Administration → Units | `units.index` | Units of measure (pcs, kg, box, carton) |
| **Price Categories** | Administration → Price Categories | `price-categories.index` | Pricing tiers (Retail, Wholesale, VIP) |
| **Product Categories**| Administration → Product Categories | `product-categories.index`| Product classification tree |
| **Brands** | Administration → Brands | `brands.index` | Brand & trademark catalog |
| **Settings / Branding** | Administration → Settings | `settings.index` | Company info, logos, themes, demo reset & roles|
| **ZATCA Compliance** | Administration → ZATCA Compliance | `zatca-compliance.index`| Phase 2 e-invoicing CSR, certs & private keys |
| **Document Shares** | Top Bar / Documents | `document-shares.index` | Manage shared links, token expiry & revocation |

---

## 🔄 Core Business Cycles

### Cycle 1: Sales Cycle (Sales → Cash)

```text
┌─────────────────────────────────────────────────────────────┐
│                   SALES CYCLE FLOWCHART                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. CUSTOMER SELECTION / CREATION                           │
│     └─→ Search existing customer or create new              │
│     └─→ Check credit limit & outstanding balance            │
│                                                             │
│  2. POS SALE OR BACK-OFFICE INVOICE                         │
│     ├─→ Scan barcode or pick items from catalog             │
│     ├─→ System applies Unit & Price Category pricing        │
│     ├─→ Auto-calculates 15% VAT                             │
│     └─→ Payment type: Cash, Card, or Credit Account         │
│                                                             │
│  3. AUTOMATED POSTING & QR ENCODING                         │
│     ├─→ Generates ZATCA TLV Base64 QR code                  │
│     ├─→ Shop inventory decremented immediately              │
│     ├─→ GL posted: Debit Cash/AR, Credit Revenue, Credit VAT│
│     └─→ COGS posted: Debit COGS, Credit Inventory (at WAC)  │
│                                                             │
│  4. RECEIPT & SHARING                                       │
│     ├─→ Print POS receipt or PDF invoice                    │
│     ├─→ Generate secure public share link with token        │
│     └─→ Optional email dispatch                             │
│                                                             │
│  5. AR SETTLEMENT (FOR CREDIT SALES)                        │
│     ├─→ View in Customer Receivables / AR Aging             │
│     ├─→ Record partial or full payment (Cash/Bank/Cheque)   │
│     └─→ GL posted: Debit Cash/Bank, Credit AR               │
│                                                             │
│  END: Customer balance updated, books balanced              │
└─────────────────────────────────────────────────────────────┘
```

### Cycle 2: Purchasing Cycle (PO → Payment)

```text
┌─────────────────────────────────────────────────────────────┐
│                 PURCHASING CYCLE FLOWCHART                  │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. PURCHASE ORDER CREATION                                 │
│     ├─→ Select Supplier & Target Central Warehouse          │
│     ├─→ Add products, quantities & agreed unit cost         │
│     ├─→ Set expected delivery date                          │
│     └─→ Status: DRAFT → SUBMITTED                           │
│                                                             │
│  2. GOODS RECEIPT (FULL OR PARTIAL)                         │
│     ├─→ Warehouse verifies physical delivery                │
│     ├─→ Record received quantities                          │
│     ├─→ PO status: PARTIALLY_RECEIVED or RECEIVED           │
│     ├─→ Warehouse inventory atomically increased            │
│     └─→ Product Weighted Average Cost (WAC) recalculated    │
│                                                             │
│  3. SUPPLIER BILL GENERATION (3-WAY MATCH)                  │
│     ├─→ System auto-generates Supplier Bill from receipt    │
│     ├─→ Bill status: UNPAID / AWAITING_PAYMENT              │
│     └─→ GL posted: Debit Inventory, Debit Input VAT,        │
│                    Credit Accounts Payable (AP)             │
│                                                             │
│  4. PAYMENT EXECUTION                                       │
│     ├─→ View in Supplier Bills / AP Aging                   │
│     ├─→ Record payment via Bank, Cash, or Issued Cheque     │
│     └─→ GL posted: Debit AP, Credit Cash/Bank               │
│                                                             │
│  END: Stock in warehouse, vendor bill cleared               │
└─────────────────────────────────────────────────────────────┘
```

### Cycle 3: Inventory Transfer Cycle (Warehouse → Shop)

```text
┌─────────────────────────────────────────────────────────────┐
│              STOCK TRANSFER CYCLE FLOWCHART                 │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. CREATE STOCK TRANSFER                                   │
│     ├─→ Source: Central Warehouse                           │
│     ├─→ Destination: Target Shop (e.g. Riyadh Branch)       │
│     ├─→ Add items & transfer quantities                     │
│     ├─→ System validates stock availability & locks row     │
│     └─→ Status: PENDING                                     │
│                                                             │
│  2. DISPATCH SHIPMENT                                       │
│     ├─→ Warehouse team picks and inspects goods             │
│     ├─→ Status becomes: IN_TRANSIT                          │
│     └─→ Warehouse stock reserved / deducted                 │
│                                                             │
│  3. SHOP RECEIVE & CONFIRMATION                             │
│     ├─→ Shop manager inspects delivered physical items      │
│     ├─→ Confirms received count at shop dock                │
│     ├─→ Status becomes: COMPLETED                           │
│     └─→ Shop local inventory atomically incremented         │
│                                                             │
│  END: Stock available at shop POS for cashier sales         │
└─────────────────────────────────────────────────────────────┘
```

### Cycle 4: Returns & Credit/Debit Notes Cycle

```text
┌─────────────────────────────────────────────────────────────┐
│                 RETURNS & REFUNDS FLOWCHART                 │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  A. CUSTOMER RETURN (CREDIT NOTE):                          │
│     ├─→ Navigate to Sales → Credit Notes → Create           │
│     ├─→ Select Customer & Return Items/Quantities           │
│     ├─→ Enter Return Reason (Defective, Wrong Item, etc.)   │
│     ├─→ Save Credit Note:                                   │
│     │   • Shop stock incremented (goods returned)           │
│     │   • GL: Debit Sales Returns & VAT, Credit AR/Cash     │
│     │   • COGS Reversal: Debit Inventory, Credit COGS       │
│     └─→ Customer receivable balance decreased or cash refund│
│                                                             │
│  B. SUPPLIER RETURN (DEBIT NOTE):                           │
│     ├─→ Navigate to Purchasing → Debit Notes → Create       │
│     ├─→ Select Supplier & Original Purchase Reference       │
│     ├─→ Enter Returned Items & Cost                         │
│     ├─→ Save Debit Note:                                    │
│     │   • Warehouse stock decremented (goods returned)      │
│     │   • GL: Debit Accounts Payable,                       │
│     │         Credit Inventory, Credit Input VAT            │
│     └─→ Supplier bill liability reduced                     │
│                                                             │
│  END: Inventories accurate, financial statements adjusted   │
└─────────────────────────────────────────────────────────────┘
```

### Cycle 5: Cheques Register Cycle (Post-Dated Cheques)

```text
┌─────────────────────────────────────────────────────────────┐
│               CHEQUES REGISTER LIFECYCLE                    │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. RECORD CHEQUE ENTRY                                     │
│     ├─→ Type: RECEIVED (from Customer) or ISSUED (to Vendor)│
│     ├─→ Cheque Number, Due Date, Bank, Amount & Payee       │
│     ├─→ Status: PENDING / ON_HAND                           │
│     └─→ GL: Posted to "Cheques Under Collection" account    │
│                                                             │
│  2. BANK DEPOSIT                                            │
│     ├─→ Cheque presented to bank on/after due date          │
│     └─→ Status: DEPOSITED                                   │
│                                                             │
│  3A. CLEARED                                                │
│     ├─→ Bank confirms funds transfer                        │
│     ├─→ Click "Clear Cheque"                                │
│     ├─→ Status: CLEARED                                     │
│     └─→ GL: Debit Main Bank Account,                        │
│             Credit Cheques Under Collection                 │
│                                                             │
│  3B. BOUNCED / DISHONORED                                   │
│     ├─→ Insufficient funds / signature mismatch             │
│     ├─→ Click "Bounce Cheque"                               │
│     ├─→ Status: BOUNCED                                     │
│     └─→ GL: Reverses collection, restores original AR/AP    │
│                                                             │
│  END: Full audit trail of cheque lifecycle preserved        │
└─────────────────────────────────────────────────────────────┘
```

### Cycle 6: Bank Reconciliation Cycle

```text
┌─────────────────────────────────────────────────────────────┐
│            BANK RECONCILIATION WORKFLOW                     │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  1. SELECT BANK ACCOUNT                                     │
│     └─→ Accounting → Bank Reconciliation → Select Account   │
│                                                             │
│  2. IMPORT BANK STATEMENT CSV                               │
│     ├─→ Upload bank CSV file (Date, Description, Amount)    │
│     └─→ System parses statement rows into staging table     │
│                                                             │
│  3. AUTO-MATCH & MANUAL MATCH                               │
│     ├─→ System auto-matches identical amounts & dates       │
│     ├─→ Operator manually matches batch deposits/fees       │
│     └─→ Identifies unpresented cheques & deposits in transit│
│                                                             │
│  4. RECORD ADJUSTMENTS                                      │
│     ├─→ Post bank service charges & interest journal entries│
│     └─→ Reconciled Difference = 0.00 SAR                    │
│                                                             │
│  5. FINALIZE RECONCILIATION                                 │
│     └─→ Sign off statement period & lock reconciliation     │
│                                                             │
│  END: GL Bank balance matches verified bank statement       │
└─────────────────────────────────────────────────────────────┘
```

---

## 📱 Screen Navigation Trees

### 1. Operations Module
```text
OPERATIONS (Sidebar)
├── Shops
│   ├── All Shops (List & status)
│   ├── Create Shop (Name, slug, address)
│   └── Edit Shop (Lifecycle active/inactive)
├── Warehouses
│   ├── All Warehouses (List & capacity)
│   ├── Create Warehouse (Code, location)
│   └── Edit Warehouse
├── Products
│   ├── Catalog List (Search, filter, cost, price)
│   ├── Create Product (SKU, barcode, unit, VAT, category, brand)
│   ├── Edit Product
│   ├── Import Catalog (CSV upload → preview columns → confirm)
│   └── Export Catalog (Full CSV dump)
└── Stock Transfers
    ├── Transfer History (Filter by source/dest/status)
    ├── Create Transfer (Select warehouse → shop → items)
    └── Dispatch / Receive Action
```

### 2. Purchasing Module
```text
PURCHASING (Sidebar)
├── Suppliers
│   ├── Supplier Directory
│   ├── Create Supplier (Tax number, terms, contact)
│   └── Edit Supplier
├── Purchase Orders
│   ├── PO List (Draft, Submitted, Received, Closed)
│   ├── Create PO (Vendor, warehouse, items, expected date)
│   ├── Receive Goods (Confirm quantities & update WAC)
│   └── Print / Share PO
├── Supplier Bills
│   ├── AP Bills List (Filter by supplier & payment status)
│   └── Record Payment (Cash, bank transfer, cheque)
└── Debit Notes
    ├── Debit Notes History (Vendor return memos)
    └── Create Debit Note (Supplier, items, cost deduction)
```

### 3. Sales Module
```text
SALES (Sidebar)
├── Customers
│   ├── Customer Directory (Balances, credit limits, terms)
│   ├── Create Customer
│   └── Edit Customer
├── Customer Receivables
│   ├── Outstanding AR Invoices
│   └── Record Payment (Allocates to open sale invoices)
├── Credit Notes
│   ├── Sales Returns History
│   └── Create Credit Note (Customer, items, stock restock)
└── POS
    └── Cashier Shell (Barcode scan, cart, cash/card/credit, receipt)
```

### 4. Accounting Module
```text
ACCOUNTING (Sidebar)
├── Accounts (Chart of Accounts)
│   ├── Account Tree (Assets, Liabilities, Equity, Revenue, Expense)
│   ├── Create Account (Code, name, type, parent)
│   └── Edit Account
├── Journal Entries
│   ├── Journal History (Audit list)
│   └── Create Journal Entry (Debits = Credits integer cent validation)
├── Cheques Register
│   ├── Register List (PDC filter: Pending, Deposited, Cleared, Bounced)
│   ├── Record Cheque (Received or Issued)
│   └── Cheque Action (Deposit, Clear, Bounce)
├── Fiscal Periods
│   ├── Period Calendar (Month-by-month status)
│   └── Period Actions (Close Period / Reopen Period)
├── Bank Accounts
│   ├── Bank Registry (IBAN, bank name, account number)
│   └── Create / Edit Bank Account
└── Bank Reconciliation
    ├── Reconciliation Hub (By bank account)
    ├── Upload Statement CSV
    └── Match Transactions & Finalize
```

### 5. Reports Module
```text
REPORTS (Sidebar)
├── Reports Overview (High-level charts, sales, margins, top SKUs)
├── Trial Balance (Live GL verification, debits = credits)
├── Balance Sheet (Company-wide statement of financial position)
├── Income Statement (Revenue, COGS, gross margin, net income)
├── Cash Flow Statement (Operating, investing, financing cash flows)
├── AP Aging (Payables aging buckets: Current, 1-30, 31-60, 61-90, 90+)
└── AR Aging (Receivables aging buckets: Current, 1-30, 31-60, 61-90, 90+)
```

### 6. Administration Module
```text
ADMINISTRATION (Sidebar)
├── Users (Staff accounts, branch shop scoping & role assignment)
├── Units (Unit of measure: Pieces, Kilograms, Cartons, Meters)
├── Price Categories (Pricing tiers: Retail, Wholesale, VIP, Contractor)
├── Product Categories (Categorization taxonomy)
├── Brands (Manufacturer & trademark directories)
├── Settings / Roles / Branding
│   ├── Business Settings (Legal company name, VAT number, CR)
│   ├── Branding Management (Logo URL, Touch Icon, theme colors)
│   ├── Roles & Permissions (Dynamic role builder & permission checkboxes)
│   └── Demo Reset (Manual or scheduled demo environment reset)
└── ZATCA Compliance
    ├── Compliance Status & Certificate details
    ├── Generate CSR (Private key & certificate signing request)
    └── Download CSR & Private Key for ZATCA FATOORA Portal
```

---

## 🎯 Common Tasks & Time Estimates

| Task | Module / Action | Time | Difficulty |
|------|-----------------|------|------------|
| **Process POS Sale** | Sales → POS → Scan → Pay | 1 min | ⭐ |
| **Create New Product** | Operations → Products → Create | 3 min | ⭐ |
| **Import 500+ Products via CSV** | Operations → Products → Import → Preview → Confirm | 4 min | ⭐⭐ |
| **Create Stock Transfer** | Operations → Stock Transfers → Create → Dispatch | 3 min | ⭐⭐ |
| **Receive Transfer at Shop** | Operations → Stock Transfers → Receive | 2 min | ⭐ |
| **Create Purchase Order** | Purchasing → Purchase Orders → Create | 4 min | ⭐⭐ |
| **Receive PO & Auto-Bill** | Purchasing → Purchase Orders → Receive | 3 min | ⭐⭐ |
| **Record Supplier Payment** | Purchasing → Supplier Bills → Record Payment | 2 min | ⭐ |
| **Process Sales Return** | Sales → Credit Notes → Create | 3 min | ⭐⭐ |
| **Process Purchase Return** | Purchasing → Debit Notes → Create | 3 min | ⭐⭐ |
| **Record Received Cheque** | Accounting → Cheques → Record PDC | 2 min | ⭐ |
| **Clear or Bounce Cheque** | Accounting → Cheques → Action Button | 1 min | ⭐ |
| **Reconcile Bank Statement** | Accounting → Bank Recon → Upload CSV → Match | 10 min | ⭐⭐⭐ |
| **Post Manual Journal Entry**| Accounting → Journal Entries → Add Lines → Save | 4 min | ⭐⭐⭐ |
| **Generate ZATCA Phase 2 CSR**| Administration → ZATCA → Generate CSR & Key | 3 min | ⭐⭐ |
| **Month-End Period Close** | Accounting → Fiscal Periods → Close Period | 5 min | ⭐⭐ |
| **Share Invoice Public Link**| View Document → Share → Generate Public Link | 1 min | ⭐ |

---

## 💡 Keyboard Shortcuts & Productivity Features

### Global Hotkeys
- `Ctrl + K` / `Cmd + K`: **Command Palette** — jump immediately to any screen, report, or setting.
- `Ctrl + B` / `Cmd + B`: **Toggle Navigation Drawer** — collapse/expand the sidebar for more screen real estate.
- `Esc`: **Close active surface** (modal, drawer, command palette, or quick menu customizer).

### Top Header & Shell Controls
- **Quick Menu**: Top bar hover-intent quick launcher. Click the customize icon (sliders) to hide/unhide specific tiles.
- **Real-Time Clock**: Live synchronized business date and time in the top header.
- **Theme Switcher**: Instant cycle between Auto (system OS), Light Mode, and Dark Mode.
- **Language Switcher**: Toggle between English and Arabic (RTL) with immediate interface transformation.
- **Sidebar Scroll Persistence**: The navigation drawer remembers your exact scroll position between clicks and reloads.

---

## 🔐 User Roles & Permissions Matrix

| Permission Area | Super Admin | Accountant | Shop Manager | Cashier |
|-----------------|:-----------:|:----------:|:------------:|:-------:|
| **POS Sales & Checkout** | ✅ | ❌ | ✅ | ✅ |
| **View Hub Dashboard** | ✅ | ✅ | ✅ (Own Shop) | ❌ |
| **Product Master Catalog**| ✅ | View Only | View Only | ❌ |
| **Catalog Import/Export** | ✅ | ❌ | ❌ | ❌ |
| **Stock Transfers** | ✅ | View Only | ✅ (Own Shop) | ❌ |
| **Customer Receivables** | ✅ | ✅ | ✅ (Own Shop) | ❌ |
| **Credit Notes (Returns)**| ✅ | ✅ | ✅ (Own Shop) | ❌ |
| **Purchase Orders & Bills**| ✅ | ✅ | ❌ | ❌ |
| **Debit Notes (Returns)** | ✅ | ✅ | ❌ | ❌ |
| **General Ledger & Entries**| ✅ | ✅ | ❌ | ❌ |
| **Cheques Register** | ✅ | ✅ | ❌ | ❌ |
| **Bank Reconciliation** | ✅ | ✅ | ❌ | ❌ |
| **Financial Statements** | ✅ | ✅ | ❌ | ❌ |
| **Fiscal Period Lock/Reopen**| ✅ | ✅ | ❌ | ❌ |
| **User & Role Administration**| ✅ | ❌ | ❌ | ❌ |
| **Settings & Branding** | ✅ | ❌ | ❌ | ❌ |
| **ZATCA Certificate & CSR** | ✅ | View Only | ❌ | ❌ |

---

## 🚨 Common Alerts & Solutions

| Alert / Error | Probable Cause | Immediate Solution |
|---------------|----------------|-------------------|
| **"Journal entry not balanced"** | Total Debits ≠ Total Credits | Ensure debits and credits sum up to the exact same total in integer halalas. |
| **"Cannot post to closed fiscal period"** | Transaction date falls inside a locked period | Go to `Accounting → Fiscal Periods` to reopen the period or update entry date. |
| **"Insufficient warehouse stock for transfer"** | Requested transfer exceeds available central stock | Review warehouse inventory levels or receive pending Purchase Orders first. |
| **"Customer credit limit exceeded"** | New sale exceeds pre-configured customer credit limit | Record collection payment in `Customer Receivables` or increase customer limit. |
| **"ZATCA CSR generation failed"** | Missing organization identifiers or VAT number format | Ensure VAT number is 15 digits starting/ending with 3 in `Administration → Settings`. |
| **"Shared document token expired"** | Document public link reached its expiration date | Re-share the document from the document view screen or re-issue link. |
| **"Cheque cannot be cleared"** | Cheque is already cleared, cancelled, or missing bank link | Verify cheque current status in `Accounting → Cheques Register`. |

---

## 📈 Daily Operational Cadence

```text
08:00 AM — OPENING & READINESS
├── Log in to Back Office Dashboard
├── Review overnight KPI cards, pending transfers & cheques due today
└── Cashiers log in to POS stations with cash float

09:00 AM – 01:00 PM — MORNING TRADING & STOCK
├── POS active for customer transactions (Cash/Card/Credit)
├── Warehouse reviews pending stock transfer requests
└── Dispatch transfers to shops (status: IN_TRANSIT)

02:00 PM — PURCHASING & RECEIVING
├── Receive supplier deliveries at Central Warehouse
├── Match incoming physical stock against Purchase Orders
└── Auto-generate Supplier Bills & recalculate product costs (WAC)

04:00 PM — TREASURY & ACCOUNTING
├── Check due Post-Dated Cheques in Cheques Register (deposit/clear)
├── Import daily bank statement CSV & run Bank Reconciliation
└── Record customer collections against Customer Receivables

05:30 PM — CLOSING & RECONCILIATION
├── Close POS cashier shifts & reconcile cash drawer
├── Verify daily Sales Summary, Margin & VAT reports
└── Automatic system backup runs nightly
```

---

*SyntekPro ERP Quick Reference Guide — Version 2.0*
