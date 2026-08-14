# 🎯 SyntekPro ERP - Quick Reference & Visual Guide

> Visual diagrams, flowcharts, and quick-reference tables for common operations

---

## 📋 Quick Navigation Map

```
┌─────────────────────────────────────────────────────────────┐
│                    DASHBOARD HOME                           │
│         (First page after login - quick actions)            │
└────────┬─────────────────────────────────────────┬──────────┘
         │                                         │
         └──────────────────┬──────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
    INVENTORY         FINANCIAL            COMMERCE
    ┌───────────┐    ┌──────────┐         ┌────────────┐
    │ Products  │    │Accounting│         │ Sales      │
    │ Stock     │    │GL        │         │ Purchases  │
    │ Transfers │    │Reports   │         │ Customers  │
    │           │    │Fiscal    │         │ Suppliers  │
    └───────────┘    └──────────┘         └────────────┘
        │                │                     │
    ┌─────────────┬──────────────┬──────┬────────────────┐
    │             │              │      │                │
  Settings    Banking        Compliance POS           Reports
```

---

## 📊 Module Quick Links

| Module | Path | Purpose |
|--------|------|---------|
| **Dashboard** | Home | Overview & KPIs |
| **Inventory** | Inventory → Products | Product catalog |
| **Stock** | Inventory → Stock | Warehouse/shop stock levels |
| **Transfers** | Inventory → Stock Transfers | Move stock between locations |
| **Sales** | Commerce → Sales | Create invoices |
| **Customers** | Commerce → Customers | Customer master data |
| **Receivables** | Commerce → Receivables | Track customer payments |
| **POS** | POS → Checkout | Point of sale |
| **Purchases** | Purchasing → Purchase Orders | Create POs |
| **Suppliers** | Purchasing → Suppliers | Supplier master data |
| **Bills** | Purchasing → Supplier Bills | Track supplier invoices |
| **Payables** | Purchasing → Payables | Track supplier payments |
| **GL** | Accounting → Accounts | Chart of accounts |
| **Entries** | Accounting → Journal Entries | Manual GL postings |
| **Trial Balance** | Reports → Trial Balance | GL verification |
| **P&L** | Reports → Income Statement | Profit & loss |
| **Balance Sheet** | Reports → Balance Sheet | Financial position |
| **Bank Recon** | Banking → Reconciliation | Bank matching |
| **Cheques** | Banking → Cheques | Cheque register |
| **ZATCA** | Compliance → ZATCA | Saudi tax compliance |

---

## 🔄 Core Business Cycles

### Cycle 1: Sales Cycle (Sales → Cash)

```
┌─────────────────────────────────────────────────┐
│          SALES CYCLE FLOWCHART                  │
├─────────────────────────────────────────────────┤
│                                                 │
│  1. CREATE CUSTOMER (if new)                    │
│     └─→ Name, Address, Contact, Terms          │
│                                                 │
│  2. CREATE SALES INVOICE                        │
│     ├─→ Select Customer                         │
│     ├─→ Add line items (products/qty)           │
│     ├─→ System calculates VAT                   │
│     ├─→ Save as "Draft"                         │
│     └─→ GL entries: AR debit, Revenue credit    │
│                                                 │
│  3. SEND INVOICE TO CUSTOMER                    │
│     ├─→ Print or Email                          │
│     ├─→ Status: "Sent"                          │
│     ├─→ Includes ZATCA QR code                  │
│     └─→ Stock auto-decremented                  │
│                                                 │
│  4. RECEIVE PAYMENT                             │
│     ├─→ Cash / Cheque / Transfer                │
│     ├─→ Record via "Record Payment"             │
│     └─→ GL entries: Cash debit, AR credit       │
│                                                 │
│  5. RECONCILE                                   │
│     ├─→ Invoice marked "Paid"                   │
│     ├─→ AR aging updated                        │
│     └─→ Revenue realized                        │
│                                                 │
│  END: Customer obligation cleared               │
│       Cash received                             │
│       Revenue recorded                          │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Cycle 2: Purchasing Cycle (PO → Payment)

```
┌────────────────────────────────────────────────┐
│       PURCHASING CYCLE FLOWCHART               │
├────────────────────────────────────────────────┤
│                                                │
│  1. CREATE SUPPLIER (if new)                   │
│     └─→ Name, Address, Contact, Terms          │
│                                                │
│  2. CREATE PURCHASE ORDER                      │
│     ├─→ Select Supplier                        │
│     ├─→ Add line items (products/qty)          │
│     ├─→ Set delivery date                      │
│     ├─→ Save as "Draft"                        │
│     └─→ GL: No entries yet (encumbrance only)  │
│                                                │
│  3. SEND PO TO SUPPLIER                        │
│     ├─→ Print or Email                         │
│     └─→ Status: "Sent"                         │
│                                                │
│  4. RECEIVE GOODS                              │
│     ├─→ Match to PO                            │
│     ├─→ Verify quantities                      │
│     ├─→ Inspect for quality                    │
│     ├─→ Status: "Received"                     │
│     └─→ GL: Inventory debit, AP credit         │
│                                                │
│  5. RECEIVE SUPPLIER BILL                      │
│     ├─→ Match to PO and receipt                │
│     ├─→ Verify amounts                         │
│     ├─→ Record 3-way match                     │
│     └─→ Status: "Awaiting Payment"             │
│                                                │
│  6. RECORD PAYMENT                             │
│     ├─→ Cash / Cheque / Transfer               │
│     ├─→ Record via "Record Payment"            │
│     └─→ GL: AP debit, Cash credit              │
│                                                │
│  END: Goods received & in inventory            │
│       Bill paid                                │
│       AP cleared                               │
│                                                │
└────────────────────────────────────────────────┘
```

### Cycle 3: Inventory Cycle (Stock Movement)

```
┌──────────────────────────────────────────────────┐
│      STOCK TRANSFER CYCLE FLOWCHART              │
├──────────────────────────────────────────────────┤
│                                                  │
│  WAREHOUSE (Central Hub) - SHOP (Sales Location)│
│                                                  │
│                                                  │
│  Starting Position:                              │
│  Warehouse Stock: 500 units Widget A             │
│  Shop Stock: 50 units Widget A                   │
│                                                  │
│                                                  │
│  1. WAREHOUSE MANAGER: CREATE TRANSFER           │
│     ├─→ From: Central Warehouse                  │
│     ├─→ To: Riyadh Shop                          │
│     ├─→ Items: 100x Widget A                     │
│     ├─→ Status: PENDING                          │
│     └─→ Warehouse Stock: 500 (no change yet)     │
│                                                  │
│  2. WAREHOUSE STAFF: DISPATCH                    │
│     ├─→ Pick items from shelf                    │
│     ├─→ Verify quantity: 100 units ✓            │
│     ├─→ Pack & prepare shipping                  │
│     ├─→ Status: IN_TRANSIT                       │
│     └─→ Warehouse Stock: 500 (reserved, not yet  │
│         deducted until received)                 │
│                                                  │
│  3. COURIER: IN TRANSIT                          │
│     ├─→ Transport goods to shop                  │
│     └─→ Status: IN_TRANSIT (no changes)          │
│                                                  │
│  4. SHOP MANAGER: RECEIVE                        │
│     ├─→ Receive shipment at shop dock            │
│     ├─→ Verify: 100 units received ✓            │
│     ├─→ Check for damage                         │
│     ├─→ Status: COMPLETED                        │
│     │                                            │
│     └─→ AUTOMATIC UPDATES:                       │
│         • Warehouse Stock: 400 (-100)            │
│         • Shop Stock: 150 (+100)                 │
│         • GL entries: Debit shop stock,          │
│           Credit warehouse stock                 │
│                                                  │
│  END: Stock repositioned                         │
│       System balanced                            │
│       Ready for sales at shop                    │
│                                                  │
│                                                  │
│  NOTES:                                          │
│  • Transfer can be REVERSED at any stage         │
│  • Stock reserved until received                 │
│  • Multiple partial receipts allowed             │
│  • History maintained for audits                 │
│                                                  │
└──────────────────────────────────────────────────┘
```

### Cycle 4: Accounting Cycle (Transactions → Statements)

```
┌──────────────────────────────────────────────────┐
│      ACCOUNTING CYCLE FLOWCHART                  │
├──────────────────────────────────────────────────┤
│                                                  │
│  Month: January 2024                             │
│                                                  │
│  TRANSACTIONS THROUGHOUT MONTH:                  │
│  • Sales invoices (auto GL posting)              │
│  • Customer payments (auto GL posting)           │
│  • Purchase orders & bills (auto GL posting)     │
│  • Supplier payments (auto GL posting)           │
│  • Stock transfers (GL posting)                  │
│  • Manual journal entries (GL posting)           │
│  └─→ All posted to GL in real-time               │
│                                                  │
│  MONTH-END (Last day of January):                │
│                                                  │
│  1. REVIEW TRANSACTIONS                          │
│     └─→ Verify all month's items entered         │
│                                                  │
│  2. BANK RECONCILIATION                          │
│     ├─→ Import bank statement                    │
│     ├─→ Match to GL bank account                 │
│     ├─→ Record fees/interest                     │
│     └─→ Verify: Bank balance = GL balance       │
│                                                  │
│  3. RECORD ACCRUALS                              │
│     ├─→ Utilities (estimated)                    │
│     ├─→ Salaries (accrued)                       │
│     ├─→ Insurance allocations                    │
│     └─→ Each via journal entry                   │
│                                                  │
│  4. VERIFY TRIAL BALANCE                         │
│     ├─→ Generate Trial Balance report            │
│     ├─→ Verify: Total Debits = Credits           │
│     ├─→ Review for unusual balances              │
│     └─→ Print for records                        │
│                                                  │
│  5. GENERATE FINANCIAL STATEMENTS                │
│     ├─→ Balance Sheet                            │
│     │   └─→ Assets = Liabilities + Equity       │
│     ├─→ Income Statement                         │
│     │   └─→ Revenue - Expenses = Net Income     │
│     ├─→ Cash Flow Statement                      │
│     │   └─→ Operating/Investing/Financing       │
│     └─→ Export to PDF/Excel                      │
│                                                  │
│  6. CLOSE FISCAL PERIOD                          │
│     ├─→ Navigate to Fiscal Periods               │
│     ├─→ Select January 2024 period               │
│     ├─→ Click "Close Period"                     │
│     ├─→ Status: LOCKED (no new entries)          │
│     └─→ Archive statements for audit             │
│                                                  │
│  END OF MONTH: Complete & auditable              │
│      Next month ready to begin                   │
│                                                  │
└──────────────────────────────────────────────────┘
```

---

## 📱 Screen Navigation Trees

### Sales Module

```
COMMERCE (Top Menu)
│
├── Sales
│   ├── Create Sale
│   │   └── Select Customer → Add Items → Save → Send → Record Payment
│   ├── View All Sales
│   │   └── Search/Filter → Click to view details
│   ├── Customer Receivables
│   │   └── View aging → Click customer → Record Payment
│   └── Sales Reports
│       └── By period, customer, product
│
├── Customers
│   ├── Add Customer
│   ├── View All
│   └── Customer Details
│       └── Sales history, balance, contact
│
├── Returns
│   ├── Credit Notes (from customers)
│   └── Debit Notes (to suppliers)
│
└── POS
    ├── Checkout
    │   └── Add items → Select customer → Payment → Print
    └── Sales History
        └── View daily sales
```

### Purchasing Module

```
PURCHASING (Top Menu)
│
├── Purchase Orders
│   ├── Create PO
│   │   └── Select Supplier → Add Items → Save → Send
│   ├── View All POs
│   ├── Receive Goods
│   │   └── Verify items → Confirm receipt
│   └── PO Reports
│
├── Suppliers
│   ├── Add Supplier
│   ├── View All
│   └── Supplier Details
│       └── POs, Bills, Payments, Balance
│
├── Supplier Bills
│   ├── Create Bill
│   │   └── Link to PO → Verify → Save
│   ├── View All Bills
│   ├── Record Payment
│   │   └── Enter amount, method, date
│   └── Bill Reports
│
└── Payables
    ├── View Bills by Supplier
    └── View AP Aging
```

### Accounting Module

```
ACCOUNTING (Top Menu)
│
├── Chart of Accounts
│   ├── View Hierarchy
│   ├── Add Account
│   └── Account Details
│       └── Transactions, balance, history
│
├── Journal Entries
│   ├── Create Entry
│   │   └── Date → Add lines (Debit/Credit) → Post
│   ├── View All Entries
│   └── Entry Reports
│
├── Fiscal Periods
│   ├── Create Period
│   ├── View All
│   └── Close/Reopen Period
│
├── Banking
│   ├── Bank Accounts
│   ├── Cheques Register
│   │   └── Issue/Clear/Bounce
│   └── Bank Reconciliation
│       └── Import statement → Match → Reconcile
│
└── Reports
    ├── Trial Balance
    ├── Balance Sheet
    ├── Income Statement
    └── Cash Flow
```

---

## 🎯 Common Tasks & Time Estimates

| Task | Steps | Time | Difficulty |
|------|-------|------|------------|
| Create product | Enter details → Save → Set stock | 5 min | ⭐ |
| Create sale | Select customer → Add items → Payment | 3 min | ⭐ |
| Record customer payment | Find invoice → Record payment | 2 min | ⭐ |
| Create purchase order | Select supplier → Add items → Send | 5 min | ⭐⭐ |
| Receive goods | Match PO → Confirm receipt | 3 min | ⭐⭐ |
| Record supplier payment | Find bill → Record payment | 2 min | ⭐⭐ |
| Journal entry | Add lines → Verify balanced → Post | 5 min | ⭐⭐⭐ |
| Month-end close | Bank recon → Accruals → Statements → Close | 60 min | ⭐⭐⭐ |
| Bank reconciliation | Import statement → Match → Reconcile | 15 min | ⭐⭐ |
| POS sales | Scan items → Select payment → Print | 2 min | ⭐ |

---

## 💡 Tips & Shortcuts

### Navigation

```
🔍 Search: Press Ctrl+K to search (global search)
🔗 Back: Click browser back or use breadcrumb
📌 Favorites: Star items for quick access
🔔 Notifications: Bell icon shows alerts
⚙️ Settings: Avatar menu (top right)
```

### Data Entry

```
↹ Tab: Move to next field
⏎ Enter: Save or confirm
Esc: Cancel current action
Ctrl+S: Save form
Ctrl+P: Print current page
```

### Filtering & Sorting

```
Filter: Click filter icon to show/hide criteria
Sort: Click column header to sort ascending/descending
Search: Type in search box to filter results
Date Range: Use calendar picker
Export: Download as CSV or PDF
```

---

## 📊 Financial Terminology

### Assets (What you own)
- **Current Assets**: Can be converted to cash within 1 year
  - Cash, Bank, Accounts Receivable
- **Fixed Assets**: Long-term ownership
  - Equipment, Buildings, Vehicles

### Liabilities (What you owe)
- **Current Liabilities**: Due within 1 year
  - Accounts Payable, Short-term Loans
- **Long-term Liabilities**: Due beyond 1 year
  - Mortgages, Long-term Debt

### Equity (Owner's investment)
- Capital invested by owner
- Retained earnings (profits kept in business)

### Income Statement Terms
- **Revenue/Sales**: Money coming in
- **Cost of Goods Sold (COGS)**: Cost to make products
- **Gross Profit**: Revenue - COGS
- **Expenses**: Operating costs (rent, salaries, utilities)
- **Net Income**: Revenue - Expenses (Bottom line)

### Key Financial Ratios

```
Profitability:
  Net Profit Margin = Net Income / Revenue × 100%
  (Higher is better - shows % of revenue as profit)

Liquidity:
  Current Ratio = Current Assets / Current Liabilities
  (Should be > 1.0 - shows ability to pay debts)

Efficiency:
  Asset Turnover = Revenue / Total Assets
  (Higher is better - shows asset productivity)

Solvency:
  Debt to Equity = Total Liabilities / Total Equity
  (Lower is better - shows financial stability)
```

---

## 🔐 User Roles Summary

```
┌────────────────────────────────────────────────┐
│             ROLE PERMISSIONS MATRIX             │
├────────────────────────────────────────────────┤
│                                                │
│  SUPER ADMIN: ████████████████████████ (All)  │
│  Can do EVERYTHING in the system               │
│                                                │
│  SHOP MANAGER: ██████████░░░░░░░░░░░░ (50%)   │
│  Sales, customers, inventory (own shop only)   │
│                                                │
│  CASHIER: ████░░░░░░░░░░░░░░░░░░░░░░ (15%)   │
│  POS checkout, view sales only                 │
│                                                │
└────────────────────────────────────────────────┘

Detailed Permissions:

                        Super Admin  Manager  Cashier
Create Sales                ✓         ✓
Record Payment              ✓         ✓         ✓
Create PO                   ✓
Create Journal Entry        ✓
View Reports                ✓         ✓
Manage Users                ✓
Close Period                ✓
POS Checkout                ✓         ✓         ✓
```

---

## 🚨 Common Errors & Solutions

| Error | Cause | Solution |
|-------|-------|----------|
| "Journal entry not balanced" | Debits ≠ Credits | Verify all debit/credit amounts sum equally |
| "Stock not available" | Insufficient inventory | Check warehouse stock levels, create transfer |
| "Cannot post to closed period" | Period is locked | Reopen period or post to current period |
| "Connection refused on port 8080" | Docker not running | Run `docker compose up -d` |
| "Database connection failed" | DB container down | Run `docker compose logs db` to diagnose |
| "Customer limit exceeded" | Credit limit constraint | Verify customer credit limit or record payment |
| "File too large" | Upload limit | Try smaller file or compress |

---

## 📞 Support Contacts

```
Documentation:     See docs/ folder in repository
Community Forum:   forum.syntekpro.com
Email Support:     support@syntekpro.com
Bug Reports:       issues@syntekpro.com
Feature Requests:  features@syntekpro.com
Sales:             sales@syntekpro.com

Office Hours:      Sun-Thu, 9 AM - 5 PM (KSA Time)
Response Time:     < 24 hours
Emergency:         +966-XX-XXXX-XXXX
```

---

## ✅ Pre-Launch Checklist

Before going live, ensure:

- [ ] All warehouse stock levels verified
- [ ] Product pricing reviewed and confirmed
- [ ] Customer data imported (if migrating from old system)
- [ ] Supplier data entered
- [ ] Users created and trained
- [ ] Bank accounts configured
- [ ] ZATCA certificate installed (Saudi Arabia)
- [ ] Email configuration tested
- [ ] Backup procedures documented
- [ ] Disaster recovery plan in place
- [ ] Data entered for opening balances
- [ ] Trial balance verified before opening
- [ ] Demo transactions tested end-to-end
- [ ] Reports generated and reviewed
- [ ] Staff trained on all modules
- [ ] Documentation printed and distributed

---

## 📈 Typical Daily Schedule

```
DAILY OPERATIONS:

08:00 AM
├─ Team arrives
├─ Check Dashboard for overnight alerts
└─ Review pending actions

09:00 AM - SALES
├─ POS stations active
├─ Process customer sales
├─ Stock transfers from warehouse

12:00 PM
├─ Mid-day stock check
├─ Address customer inquiries
└─ Confirm afternoon deliveries

02:00 PM - PURCHASING
├─ Receive supplier deliveries
├─ Verify goods against PO
└─ Update inventory

04:00 PM - ACCOUNTING
├─ Record any journal entries
├─ Reconcile daily cash
├─ Process customer payments

05:00 PM - CLOSE OF DAY
├─ POS Shift Close
├─ Count cash drawers
├─ Generate daily sales report
└─ Flag any discrepancies

06:00 PM
├─ End-of-day backup (automated)
├─ Tomorrow's prep
└─ Team departs

WEEKLY:
├─ Bank reconciliation
├─ AP check run
├─ AR collections review

MONTH-END:
├─ Full accounting close
├─ Financial statements
├─ Period close & lock
```

---

## 🎓 Certification Path

To become a SyntekPro ERP Certified User:

1. **Fundamentals** (2 days)
   - Module overview
   - Navigation & basic operations
   - Quiz: 80% passing

2. **Core Modules** (5 days)
   - Sales cycle
   - Purchasing cycle
   - Inventory management
   - POS operations
   - Quiz: 80% passing

3. **Accounting** (3 days)
   - GL & journal entries
   - Month-end close
   - Financial statements
   - Quiz: 85% passing

4. **Advanced Topics** (2 days)
   - API & integrations
   - Custom reports
   - Troubleshooting
   - Project: Implement scenario

5. **Final Exam**
   - Comprehensive test
   - Practical application
   - Passing: 85%+

**Duration**: 2 weeks full-time | **Cost**: Contact sales

---

## 📚 Recommended Reading Order

1. **README_COMPLETE.md** (Overview & Architecture)
2. **TUTORIAL_GUIDE.md** (Step-by-step walkthroughs)
3. **This document** (Reference & quick lookup)
4. **docs/ folder** (Detailed module documentation)
5. **API docs** (For integrations)

---

**Version**: 1.0.0 | **Last Updated**: January 2024

*Questions? Contact support@syntekpro.com*
