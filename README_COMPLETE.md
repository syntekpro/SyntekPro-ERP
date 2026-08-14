# 📊 SyntekPro ERP Platform - Complete Guide

> **A comprehensive, multi-tenant Enterprise Resource Planning and Point-of-Sale system designed for the Saudi Arabian market.**

**Version:** 1.0.0 | **Laravel:** 12 | **PHP:** 8.2+ | **License:** MIT

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Key Features](#key-features)
3. [System Architecture](#system-architecture)
4. [Quick Start](#quick-start)
5. [Installation & Setup](#installation--setup)
6. [User Roles & Permissions](#user-roles--permissions)
7. [Modules Overview](#modules-overview)
8. [Common Workflows](#common-workflows)
9. [API Documentation](#api-documentation)
10. [Docker Deployment](#docker-deployment)
11. [Troubleshooting](#troubleshooting)
12. [Development](#development)

---

## 🎯 Overview

**SyntekPro ERP** is an enterprise-grade resource management system built specifically for Saudi Arabian businesses. It supports:

- **Multiple shops/locations** with centralized control
- **Inventory management** across warehouses and points of sale
- **Complete accounting** with journal entries, financial statements, and fiscal period management
- **Purchasing workflows** from PO through payment
- **Sales & receivables** including credit sales and payment tracking
- **ZATCA compliance** for Saudi VAT e-invoicing and QR code generation
- **Point-of-Sale (POS)** with offline-first capabilities
- **Comprehensive reporting** for financial analysis and business intelligence

### 🎨 Design Philosophy

- **Multi-tenant**: One database, shop-level isolation via `shop_id` scoping
- **Hub-owned resources**: Products, warehouses, and shops managed centrally
- **Docker-first**: Local development mirrors production exactly
- **API-driven**: RESTful endpoints for offline POS sync and integrations
- **Role-based access**: Super Admin, Shop Manager, Cashier roles with fine-grained permissions

---

## ✨ Key Features

### 📦 Inventory & Stock Management
- Central warehouse stock tracking
- Per-shop local stock management
- Stock transfer workflows (create → dispatch → receive)
- Reorder point warnings and low-stock alerts
- Multiple units of measure with conversion support
- Product categories, brands, and attributes

### 💰 Financial Management
- **Chart of Accounts**: Hierarchical GL account structure
- **Journal Entries**: Manual and auto-generated posting
- **Trial Balance**: Real-time GL reconciliation
- **Financial Statements**:
  - Balance Sheet
  - Income Statement
  - Cash Flow Statement
- **Fiscal Period Management**: Open, close, and reopen periods
- **Auto-balancing**: Prevents unbalanced journal entries

### 🛒 Sales & POS
- **Cashier POS Interface**: Responsive, fast checkout experience
- **Offline-First**: Sales sync when connection restored
- **Credit Sales**: Track customer receivables
- **Customer Payments**: Record receipts and aging analysis
- **Returns Management**: Credit notes and debit notes
- **Multi-currency**: Price categories and discounts

### 📥 Purchasing & Accounts Payable
- **Supplier Management**: Vendor profiles and payment terms
- **Purchase Orders**: Create, receive, and track
- **Supplier Bills**: Post bills and track amounts due
- **Supplier Payments**: Record payments with cheque support
- **AP Aging Report**: Track payables by age
- **Bank Reconciliation**: Match statement to GL

### 📤 Receivables & Collections
- **Customer Management**: Contact details and credit limits
- **Credit Sales**: Invoice creation and tracking
- **Customer Payments**: Record receipts by cash/cheque/transfer
- **AR Aging Report**: Track receivables by age
- **Collections Tracking**: Follow-up on overdue invoices

### 📊 Reporting & Analytics
- **Standard Reports**:
  - Trial Balance
  - AP Aging (30/60/90+ days)
  - AR Aging (30/60/90+ days)
  - Balance Sheet (multi-period)
  - Income Statement (P&L)
  - Cash Flow Statement

### ✅ ZATCA Compliance (Saudi Arabia)
- **E-invoicing**: UBL-compliant invoice generation
- **QR Codes**: TLV Base64 QR payload embedded
- **Tax Calculation**: VAT on sales and purchases
- **Compliance Reports**: ZATCA audit trail
- **CSR Generation**: Certificate Signing Request for ZATCA onboarding

### 🔐 Security & Multi-Tenancy
- **Shop Isolation**: All shop-owned data filtered by `shop_id`
- **Role-Based Access**: Super Admin, Shop Manager, Cashier
- **API Tokens**: Sanctum authentication for POS and integrations
- **Permission Matrix**: Fine-grained access control
- **Demo Mode**: Automatic nightly resets with safety guards

---

## 🏗️ System Architecture

### Database Schema Overview

```
┌─────────────────────────────────────────────────────────┐
│                    CORE ENTITIES                        │
├─────────────────────────────────────────────────────────┤
│ users              → Accounts with roles (Super Admin,   │
│                     Shop Manager, Cashier)              │
│ shops              → Individual locations/branches      │
│ warehouses         → Central inventory hubs              │
│ products           → Product catalog (hub-owned)         │
│ product_categories → Product grouping                    │
│ brands             → Supplier brands                     │
│ units              → Units of measure (kg, box, etc.)   │
│ price_categories   → Tiered pricing profiles            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                   INVENTORY LAYER                       │
├─────────────────────────────────────────────────────────┤
│ warehouse_stock    → Stock at central warehouse          │
│ shop_stock         → Stock at shop/POS locations        │
│ stock_transfers    → Transfer workflows & history       │
│ stock_transfer_lines → Individual line items            │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                   SALES LAYER                           │
├─────────────────────────────────────────────────────────┤
│ sales              → Sales orders/invoices              │
│ sale_lines         → Individual items sold              │
│ sales_payments     → Payment receipts for sales         │
│ customers          → Customer master data               │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                PURCHASING LAYER                         │
├─────────────────────────────────────────────────────────┤
│ suppliers          → Vendor master data                 │
│ purchase_orders    → PO creation & tracking             │
│ supplier_bills     → Bills from suppliers               │
│ supplier_payments  → Payments to suppliers              │
│ cheques            → Cheque register & reconciliation   │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│              ACCOUNTING LAYER                           │
├─────────────────────────────────────────────────────────┤
│ accounts           → Chart of accounts (hierarchical)   │
│ journal_entries    → Manual GL postings                 │
│ journal_entry_lines → GL account entries (debits/credits)│
│ fiscal_periods     → Accounting periods (open/closed)   │
│ bank_accounts      → Bank account master                │
│ bank_statement_imports → Bank statement upload tracking │
│ bank_statement_lines   → Individual transaction lines   │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                  RETURN LAYER                           │
├─────────────────────────────────────────────────────────┤
│ credit_notes       → Returns from customers             │
│ debit_notes        → Returns to suppliers               │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│              CONFIGURATION LAYER                        │
├─────────────────────────────────────────────────────────┤
│ business_settings  → Company profile, branding, defaults│
│ document_counters  → Invoice/PO/Bill numbering sequence │
│ user_interface_preferences → Per-user UI settings      │
└─────────────────────────────────────────────────────────┘
```

### Multi-Tenancy Model

**Shared Database with Shop-Level Scoping**

```
┌─────────────────────────────────────────────────┐
│          Single Database Instance               │
├─────────────────────────────────────────────────┤
│                                                 │
│  ┌─────────────┐  ┌─────────────┐              │
│  │  Shop: 1    │  │  Shop: 2    │              │
│  │  (Riyadh)   │  │  (Jeddah)   │              │
│  ├─────────────┤  ├─────────────┤              │
│  │ Sales       │  │ Sales       │              │
│  │ Stock       │  │ Stock       │              │
│  │ Payments    │  │ Payments    │              │
│  └─────────────┘  └─────────────┘              │
│                                                 │
│  ┌──────────────────────────────┐              │
│  │ Hub-Owned (No shop_id)       │              │
│  │ - Products                   │              │
│  │ - Warehouses                 │              │
│  │ - Shops (configuration)      │              │
│  │ - Users (with shop assignment)│             │
│  └──────────────────────────────┘              │
│                                                 │
└─────────────────────────────────────────────────┘
```

### Application Stack

```
┌──────────────────────────────────────────────────┐
│         Frontend (Browser)                       │
│  ┌────────────────────────────────────────────┐  │
│  │  Blade Templates + Livewire 3              │  │
│  │  Tailwind CSS v4                           │  │
│  │  Vite Asset Pipeline                       │  │
│  └────────────────────────────────────────────┘  │
└───────────────────┬────────────────────────────────┘
                    │ HTTP/REST
┌───────────────────┴────────────────────────────────┐
│         Backend (Docker Container)                 │
│  ┌────────────────────────────────────────────┐   │
│  │  Laravel 12 Application                    │   │
│  │  - Controllers (request handling)          │   │
│  │  - Services (business logic)               │   │
│  │  - Models (data access)                    │   │
│  │  - Policies (authorization)                │   │
│  │  - Events/Jobs (async operations)          │   │
│  └────────────────────────────────────────────┘   │
│                    ↓                               │
│  ┌────────────────────────────────────────────┐   │
│  │  PHP-FPM Runtime (port 9000)               │   │
│  │  Served by Nginx Reverse Proxy (port 80)   │   │
│  └────────────────────────────────────────────┘   │
└───────────────────┬────────────────────────────────┘
                    │
        ┌───────────┴───────────┬─────────────┐
        ↓                       ↓             ↓
  ┌──────────────┐       ┌──────────┐  ┌──────────┐
  │ MariaDB      │       │ Redis    │  │ Storage  │
  │ (Persistent)│       │ (Cache)  │  │ (Files)  │
  └──────────────┘       └──────────┘  └──────────┘
```

---

## 🚀 Quick Start

### Prerequisites

- Docker & Docker Compose (v20.10+)
- Git
- 2GB+ free disk space
- 2GB+ available RAM

### 5-Minute Setup

```bash
# 1. Clone or navigate to repository
cd SyntekPro-ERP

# 2. Copy environment configuration
cp .env.example .env

# 3. Start Docker containers
docker compose up -d --build

# 4. Install dependencies (PHP & Node)
docker compose run --rm composer install
docker compose run --rm node install

# 5. Generate app key and migrate database
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed

# 6. Build frontend assets
docker compose run --rm node run build

# 7. Open in browser
# → http://localhost:8080

# Default login credentials:
# Email: development@syntekpro.com
# Password: password
```

✅ **Done!** Your ERP is now running.

---

## 📦 Installation & Setup

### Full Installation Guide

#### Step 1: Environment Configuration

```bash
# Copy example environment file
cp .env.example .env

# Edit .env with your settings
# Key variables:
APP_NAME="Your Company Name"
APP_ENV=local|production
APP_DEBUG=true|false
APP_URL=http://localhost:8080

DB_HOST=db
DB_DATABASE=syntekpro
DB_USERNAME=syntekpro
DB_PASSWORD=secretpassword

REDIS_HOST=redis

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password

# ZATCA Configuration (for Saudi VAT compliance)
ZATCA_SELLER_LEGAL_NAME="Your Legal Company Name"
ZATCA_SELLER_VAT_NUMBER="311111111111111"
```

#### Step 2: Docker Initialization

```bash
# Build and start all services
docker compose up -d --build

# Verify services are running
docker compose ps
# Expected output:
# - syntekpro-app-1     (PHP-FPM, running)
# - syntekpro-web-1     (Nginx, running)
# - syntekpro-db-1      (MariaDB, running)
# - syntekpro-redis-1   (Redis, running)
```

#### Step 3: Database Setup

```bash
# Install Composer dependencies
docker compose run --rm composer install

# Run migrations with seed data
docker compose exec app php artisan migrate --seed

# Create symbolic storage link
docker compose exec app php artisan storage:link
```

#### Step 4: Frontend Build

```bash
# Install Node dependencies
docker compose run --rm node install

# Build Vite assets
docker compose run --rm node run build

# For development (hot reload):
docker compose run --rm --service-ports node run dev -- --host 0.0.0.0
```

#### Step 5: Create Super Admin (Optional - if not using seeds)

```bash
docker compose exec app php artisan tinker

# In Tinker REPL:
>>> use App\Models\User;
>>> User::create([
    'name' => 'Admin',
    'email' => 'admin@company.com',
    'password' => Hash::make('password123'),
    'role' => 'super_admin'
])
```

### Verify Installation

```bash
# Check if app is accessible
curl http://localhost:8080

# View Laravel logs
docker compose logs app

# Check database connectivity
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo()
# Should return PDO object

# Run test suite
docker compose exec app php artisan test
```

---

## 👥 User Roles & Permissions

### Role Hierarchy

```
┌─────────────────────────────────────────────────┐
│          SUPER ADMIN                            │
│  ✓ Full system access                           │
│  ✓ Create/manage shops                          │
│  ✓ Create/manage warehouses                     │
│  ✓ Manage users and roles                       │
│  ✓ Configure business settings                  │
│  ✓ View all reports                             │
│  ✓ Manage accounting, fiscal periods            │
│  ✓ System configuration                         │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│          SHOP MANAGER                           │
│  ✓ Assigned to one or more shops                │
│  ✓ Create sales and manage stock for assigned   │
│    shops only                                   │
│  ✓ View shop-level reports                      │
│  ✓ Manage customers and suppliers              │
│  ✓ Cannot create warehouses or manage other    │
│    shops                                        │
│  ✓ Cannot access accounting/GL                 │
└─────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────┐
│          CASHIER                                │
│  ✓ Assigned to specific shop(s)                 │
│  ✓ Process sales and payments only              │
│  ✓ Cannot create new customers or modify       │
│    inventory                                    │
│  ✓ Limited to POS checkout interface           │
│  ✓ No reporting or configuration access        │
└─────────────────────────────────────────────────┘
```

### Creating Users

**Via Dashboard (Super Admin only):**
1. Navigate to Settings → Users
2. Click "Add User"
3. Enter email, name, and assign role
4. Set shop assignments (for managers/cashiers)
5. System generates temporary password
6. User receives email with login link

**Via Command Line:**
```bash
docker compose exec app php artisan make:user \
  --name="John Doe" \
  --email="john@company.com" \
  --role="shop_manager" \
  --shop=1
```

---

## 📊 Modules Overview

### 1. Dashboard

The main entry point showing key metrics and quick actions.

**Super Admin View:**
- Total shops, warehouses, products
- Total sales (period-based)
- Stock value and movements
- AR/AP aging summary
- Recent transactions

**Shop Manager View:**
- Shop-specific sales metrics
- Local stock levels
- Top-selling products
- Daily/weekly/monthly trends
- Pending actions (POs, bills)

**Cashier View:**
- Quick POS access
- Sales for current shift
- Cash reconciliation
- Top products by transaction

### 2. Inventory Management

#### Warehouse & Stock Setup
1. **Navigate:** Settings → Warehouses
2. **Create Warehouse:**
   - Name: "Central Warehouse - Riyadh"
   - Location: Warehouse address
   - Contact: Responsible person
3. **Add Stock:**
   - Navigate to Products
   - Set warehouse stock levels for each product
   - System tracks movements automatically

#### Stock Transfers (Transfer Between Warehouse and Shop)

**Create Transfer:**
1. Navigate: Stock Management → Stock Transfers
2. Click "Create Transfer"
3. Select source (warehouse/shop) and destination (shop/warehouse)
4. Add line items:
   - Product
   - Quantity
   - Unit
5. Submit ("Pending" status)

**Dispatch Transfer:**
1. Warehouse manager views transfer in "Pending" status
2. Verifies items are picked and packed
3. Clicks "Dispatch" (moves to "In Transit")
4. System records pickup timestamp

**Receive Transfer:**
1. Shop manager/receiving staff views transfer in "In Transit"
2. Verifies items received match invoice
3. Clicks "Receive" (moves to "Completed")
4. Local stock automatically incremented
5. Warehouse stock decremented

**Full Workflow Diagram:**
```
CREATE (Pending)
   ↓
DISPATCH (In Transit) [Warehouse confirms items picked]
   ↓
RECEIVE (Completed) [Shop confirms items received]
   ↓
Stock levels updated in both locations
```

### 3. Point of Sale (POS)

#### Cashier Workflow

**1. Start Cashier Session**
- Navigate: POS → Checkout
- System loads shop stock and pricing tiers

**2. Create Sale**
```
┌──────────────────────────────────┐
│  Customer Selection              │
│  [Select Existing] [New Customer]│
└─────────────────┬────────────────┘
                  ↓
┌──────────────────────────────────┐
│  Scan / Search Products          │
│  SKU: [_______________]          │
│  Or Browse Categories            │
└─────────────────┬────────────────┘
                  ↓
┌──────────────────────────────────┐
│  Add Item to Cart                │
│  Product: Widget A               │
│  Qty: [2]  Unit Price: 100 SAR   │
│  Line Total: 200 SAR             │
│  [Add] [Remove]                  │
└─────────────────┬────────────────┘
                  ↓
┌──────────────────────────────────┐
│  Apply Discounts (if authorized) │
│  % Discount or Fixed Amount      │
└─────────────────┬────────────────┘
                  ↓
┌──────────────────────────────────┐
│  Select Payment Method           │
│  ☑ Cash    ☐ Card   ☐ Cheque    │
└─────────────────┬────────────────┘
                  ↓
┌──────────────────────────────────┐
│  Complete Payment                │
│  Total: 200 SAR                  │
│  [Tendered: 200]  [Complete]     │
│  Change: 0 SAR                   │
└─────────────────┬────────────────┘
                  ↓
            INVOICE PRINTED
            Receipt emailed (if configured)
```

**3. Offline Mode**
- All transactions stored locally
- Sync when connection restored
- Idempotent API prevents duplicate charges

**4. Reprint Receipt**
- Navigate: POS → Sales History
- Select transaction
- Click "Print Receipt" or "Email"

### 4. Sales & Receivables

#### Create Credit Sale

1. Navigate: Sales → Create
2. Select Customer (create if new):
   - Name, Contact, Address
   - Payment Terms (due date calculation)
3. Add Line Items:
   - Select Product
   - Enter Quantity
   - Unit price auto-populated from price category
   - Add notes (optional)
4. Review & Save (status: "Draft")
5. Print/Email to customer (status changes to "Sent")
6. Customer receives invoice with QR code (ZATCA compliant)

#### Record Customer Payment

1. Navigate: Receivables → Customer Receivables
2. View customer with outstanding balance
3. Click "Record Payment"
4. Enter:
   - Amount received
   - Payment method (cash, cheque, transfer)
   - Reference (cheque #, transfer reference)
   - Date received
5. Save (auto-applies to oldest invoices)
6. AR Aging automatically updated

#### AR Aging Report

Navigate: Reports → AR Aging

```
Customer Name    │ Total Due │  Current  │ 30-60 Days │ 60-90 Days │ >90 Days
─────────────────┼───────────┼───────────┼────────────┼────────────┼──────────
ABC Corp         │ 15,000    │ 10,000    │ 5,000      │ -          │ -
XYZ Ltd          │ 8,500     │ -         │ -          │ 3,500      │ 5,000
Retail Shop      │ 2,250     │ 2,250     │ -          │ -          │ -
─────────────────┼───────────┼───────────┼────────────┼────────────┼──────────
TOTAL            │ 25,750    │ 12,250    │ 5,000      │ 3,500      │ 5,000
```

### 5. Purchasing & Accounts Payable

#### Purchase Order Workflow

```
CREATE PO (Draft)
   ↓
SUBMIT (Sent) → Sent to supplier
   ↓
RECEIVE ITEMS (Partial/Full)
   ↓
RECEIVE BILL (Matches PO)
   ↓
RECORD PAYMENT (Settles bill)
```

**Create Purchase Order:**
1. Navigate: Purchasing → Purchase Orders → Create
2. Select Supplier
3. Add line items:
   - Product
   - Quantity required
   - Unit price
   - Delivery date
4. Save (status: Draft)
5. Print/Email to supplier (status: Sent)
6. Supplier delivers goods

**Receive Goods:**
1. Navigate: Purchasing → Purchase Orders
2. Select PO
3. Click "Receive" on each line item
4. Verify quantity received
5. Update warehouse stock
6. Goods now in inventory

**Process Supplier Bill:**
1. Navigate: Purchasing → Bills → Create
2. Link to Purchase Order (auto-populates lines)
3. Verify amounts match PO
4. Add any additional charges (shipping, etc.)
5. Save (status: "Received")
6. Awaiting Payment

**Record Payment:**
1. Navigate: Payables → Bills
2. Select bill with status "Awaiting Payment"
3. Click "Record Payment"
4. Enter:
   - Amount paying
   - Payment method (cash, cheque, transfer)
   - Reference (cheque #, transfer details)
5. Save (bill marked as paid)

#### AP Aging Report

Navigate: Reports → AP Aging

```
Supplier Name    │ Total Due │  Current  │ 30-60 Days │ 60-90 Days │ >90 Days
─────────────────┼───────────┼───────────┼────────────┼────────────┼──────────
Supplier A       │ 50,000    │ 50,000    │ -          │ -          │ -
Supplier B       │ 30,000    │ -         │ 15,000     │ 15,000     │ -
Supplier C       │ 12,000    │ -         │ -          │ 12,000     │ -
─────────────────┼───────────┼───────────┼────────────┼────────────┼──────────
TOTAL            │ 92,000    │ 50,000    │ 15,000     │ 27,000     │ -
```

### 6. Accounting & Financial Management

#### Chart of Accounts

Navigate: Accounting → Accounts

**Account Hierarchy:**
```
1000 - ASSETS
├── 1100 - Current Assets
│   ├── 1110 - Cash
│   ├── 1120 - Bank Accounts
│   └── 1130 - Accounts Receivable
├── 1200 - Fixed Assets
│   ├── 1210 - Property & Equipment
│   └── 1220 - Accumulated Depreciation
│
2000 - LIABILITIES
├── 2100 - Current Liabilities
│   ├── 2110 - Accounts Payable
│   ├── 2120 - Short-term Debt
│   └── 2130 - Accrued Expenses
│
3000 - EQUITY
├── 3100 - Capital
├── 3200 - Retained Earnings
│
4000 - REVENUE
├── 4100 - Sales Revenue
├── 4200 - Service Revenue
│
5000 - EXPENSES
├── 5100 - Cost of Goods Sold
├── 5200 - Salaries & Wages
├── 5300 - Utilities
```

#### Manual Journal Entry

**Create Entry:**
1. Navigate: Accounting → Journal Entries → Create
2. Enter:
   - Entry Date
   - Reference (document #)
   - Description
3. Add line items:
   - Account (e.g., "Cash")
   - Debit or Credit amount
   - Description
4. System validates: **Total Debits = Total Credits**
5. Submit (auto-posts to GL)

**Example: Record rent expense paid by cheque**
```
Debit  5300 - Rent Expense        5,000
Credit 1120 - Bank Account                 5,000
```

#### Financial Statements

**Trial Balance:**
- Navigate: Reports → Trial Balance
- Shows all GL accounts with debits and credits
- Validates: Total Debits = Total Credits
- Filter by date range and fiscal period

**Balance Sheet:**
- Navigate: Reports → Balance Sheet
- Shows: Assets = Liabilities + Equity
- Period comparison (current vs prior year)
- Drill-down into account balances

**Income Statement:**
- Navigate: Reports → Income Statement
- Shows: Revenue - Expenses = Net Income
- Period comparison and variance analysis
- Segment by department/shop (if configured)

**Cash Flow Statement:**
- Navigate: Reports → Cash Flow
- Shows movement of cash through:
  - Operating activities
  - Investing activities
  - Financing activities

#### Fiscal Period Management

**Setup:**
1. Navigate: Accounting → Fiscal Periods
2. Click "Create Period"
3. Enter:
   - Period Name (e.g., "Jan 2024")
   - Start Date: 2024-01-01
   - End Date: 2024-01-31
4. System prevents posting to closed periods

**Close Period:**
1. Navigate: Accounting → Fiscal Periods
2. Select period
3. Verify Trial Balance (must be balanced)
4. Click "Close Period"
5. System locks period (no further entries allowed)
6. Auto-generates closing entries (if configured)

**Reopen (if needed):**
1. Click "Reopen Period"
2. Allows corrections
3. Usually restricted to Admin

### 7. Cheques Management

#### Register Cheque

1. Navigate: Banking → Cheques → Create
2. Enter cheque details:
   - Cheque #
   - Bank account
   - Amount
   - Payee/Issue date
   - Purpose (reference to bill/sale)
3. Save (status: "Issued")

#### Clear Cheque

1. When cheque clears bank:
   - Navigate: Banking → Cheques
   - Select cheque
   - Click "Clear"
   - Status: "Cleared"
   - Matched to bank statement

#### Bounce Cheque

1. If cheque bounces:
   - Navigate: Banking → Cheques
   - Select cheque
   - Click "Bounce"
   - Status: "Bounced"
   - System records bounce date
   - Creates journal entry to reverse original posting

### 8. Bank Reconciliation

**Import Bank Statement:**
1. Navigate: Banking → Bank Reconciliation
2. Download statement from bank (CSV format)
3. Click "Import Statement"
4. Map CSV columns:
   - Transaction Date
   - Description
   - Amount (or separate Debit/Credit)
   - Reference
   - Running Balance
5. Upload & import

**Reconcile Transactions:**
1. View imported statement lines
2. Match each line to GL transactions
3. Click to auto-match or manual match
4. Identify outstanding cheques and deposits in transit
5. Verify bank balance matches GL bank account balance
6. Print reconciliation report

### 9. ZATCA Compliance (Saudi Arabia)

#### E-Invoice Generation

All invoices automatically include:
- **UBL Format**: UN/CEFACT standard
- **QR Code**: TLV Base64 encoded payload containing:
  - Seller VAT number
  - Invoice UUID
  - Invoice total
  - Invoice date/time
  - Invoice hash

#### CSR Generation (Onboarding)

1. Navigate: Compliance → ZATCA Setup
2. Enter:
   - Legal entity name
   - VAT number (15-digit)
   - Organization identifier
3. Click "Generate CSR"
4. System creates Certificate Signing Request
5. Download and submit to ZATCA portal
6. Receive certificate
7. Upload certificate back to system

#### VAT Calculation

- System automatically calculates VAT on sales
- Applies configured VAT rate (typically 15% for Saudi Arabia)
- Tracks VAT liability by period
- Generates VAT return report (for ZATCA submission)

#### ZATCA Reports

1. Navigate: Reports → ZATCA Compliance
2. View:
   - Invoices issued (by period)
   - Credit notes issued
   - Debit notes received
   - VAT liability summary
   - Invoice hash chain validation
   - e-invoice transmission status

---

## 🔄 Common Workflows

### Workflow 1: Complete Sales Cycle (from PO to Payment)

```
Day 1 - Monday: Create Purchase Order
├─ Navigate: Purchasing → PO → Create
├─ Select Supplier: "ABC Supplies Ltd"
├─ Add Items: 100 units Widget A @ 50 SAR each
├─ Delivery Date: Friday
└─ Status: Sent to supplier

Day 3 - Wednesday: Goods Arrive
├─ Navigate: Receiving → PO
├─ Click "Receive" on PO
├─ Verify: 100 units received
├─ Update Stock: Now in warehouse
└─ Status: Received

Day 4 - Thursday: Receive Invoice
├─ Navigate: Payables → Create Bill
├─ Link to PO (auto-populates items)
├─ Verify amount: 5,000 SAR (100 × 50)
├─ Save
└─ Status: Awaiting Payment

Day 5 - Friday: Pay Supplier
├─ Navigate: Payables → Bills
├─ Click "Record Payment"
├─ Amount: 5,000 SAR
├─ Method: Bank Transfer
├─ Reference: Transfer Reference #
└─ Status: Paid (closes bill)

Day 7 - Sunday: Sell to Customer
├─ Navigate: POS → Checkout
├─ Add 10 units Widget A to sale
├─ Customer: ABC Retail
├─ Method: Cash
├─ Total: 1,000 SAR (10 × 100 retail price)
└─ Stock: Automatically decremented by 10

End of Month: Accounting Close
├─ Navigate: Reports → Trial Balance
├─ Verify: Balanced
├─ Navigate: Accounting → Fiscal Periods
├─ Click: Close Period
└─ Period locked for future entry prevention
```

### Workflow 2: Multi-Location Stock Movement

```
Scenario: Transfer 50 units from Central Warehouse to Riyadh Shop

STEP 1: Warehouse Manager - Create Transfer (Monday)
├─ Navigate: Stock → Transfers → Create
├─ From: Central Warehouse
├─ To: Riyadh Shop
├─ Add Item: 50x Widget A
├─ Notes: "Weekly stock replenishment"
└─ Status: PENDING

STEP 2: Warehouse Staff - Dispatch (Monday Afternoon)
├─ Navigate: Stock → Transfers
├─ View PENDING transfers
├─ Select transfer
├─ Verify: 50 units picked and packed
├─ Click: DISPATCH
├─ Update notes: "Handed to courier #12345"
└─ Status: IN_TRANSIT

STEP 3: Courier - In Transit (Tuesday)
├─ Goods in transit to Riyadh
└─ Status remains: IN_TRANSIT

STEP 4: Riyadh Shop Manager - Receive (Tuesday Afternoon)
├─ Navigate: Stock → Transfers
├─ View IN_TRANSIT transfers
├─ Click: RECEIVE
├─ Verify items received:
│  ├─ Count: 50 units ✓
│  ├─ Condition: OK ✓
│  └─ Notes: "Received intact"
└─ Status: COMPLETED

AUTOMATIC UPDATES:
├─ Central Warehouse Stock: -50 units Widget A
├─ Riyadh Shop Stock: +50 units Widget A
├─ GL Postings (if configured):
│  ├─ Debit: Shop Stock
│  └─ Credit: Warehouse Stock
└─ Stock movement history recorded
```

### Workflow 3: Month-End Accounting Close

```
Date: Last day of month

STEP 1: Verify All Transactions Posted (Finance Manager)
├─ Navigate: Reports → Trial Balance
├─ Check: All transactions for month entered
├─ Review: Journal entries for corrections
└─ Ensure: No pending unposted items

STEP 2: Reconcile Bank Account (Accountant)
├─ Navigate: Banking → Reconciliation
├─ Import bank statement from bank
├─ Match:
│  ├─ Deposits in transit
│  ├─ Outstanding cheques
│  └─ Bank errors/fees
├─ Verify: GL bank balance = Statement balance
└─ Print: Reconciliation report

STEP 3: Review Receivables & Payables (Collections/Payments)
├─ AR Aging:
│  ├─ Navigate: Reports → AR Aging
│  ├─ Identify overdue invoices
│  └─ Create collection plan
├─ AP Aging:
│  ├─ Navigate: Reports → AP Aging
│  ├─ Schedule payments
│  └─ Ensure vendor relationships
└─ Both reports filed for audit trail

STEP 4: Record Month-End Adjustments (Accountant)
├─ Depreciation:
│  ├─ Navigate: Accounting → Journal Entries
│  ├─ Calculate: Monthly depreciation
│  └─ Post: Debit Depreciation Exp / Credit Accumulated Dep
├─ Accruals:
│  ├─ Utilities used but not billed
│  ├─ Salaries accrued
│  └─ Post similar entries
└─ Review and approve each entry

STEP 5: Generate Financial Statements (Finance Manager)
├─ Trial Balance:
│  ├─ Navigate: Reports → Trial Balance
│  ├─ Verify: Total Debits = Total Credits
│  └─ Print/export for records
├─ Balance Sheet:
│  ├─ Navigate: Reports → Balance Sheet
│  ├─ Verify: Assets = Liabilities + Equity
│  └─ Export to CSV/PDF
├─ Income Statement:
│  ├─ Navigate: Reports → Income Statement
│  ├─ Analyze: Revenue vs Expenses, Net Income
│  └─ Export to CSV/PDF
└─ Cash Flow:
   ├─ Navigate: Reports → Cash Flow
   ├─ Analyze: Cash movement during period
   └─ Export to CSV/PDF

STEP 6: Close Fiscal Period (Finance Manager/Admin)
├─ Navigate: Accounting → Fiscal Periods
├─ Select: Current month period
├─ Verify: Trial Balance shows balanced
├─ Click: CLOSE PERIOD
├─ System locks period
└─ No more entries allowed for this month

COMPLETED:
├─ All adjustments made
├─ Statements generated
├─ Period closed
└─ Ready for next month
```

---

## 🔌 API Documentation

### Authentication

All API requests require Sanctum token:

```bash
# Generate token for POS device
curl -X POST http://localhost:8080/api/tokens \
  -H "Content-Type: application/json" \
  -d '{
    "email": "pos@shop.com",
    "password": "password",
    "device_name": "POS Terminal 1"
  }'

# Response:
{
  "plainTextToken": "1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
}

# Use token in requests:
curl -X GET http://localhost:8080/api/sales \
  -H "Authorization: Bearer 1|aBcDeFgHiJkLmNoPqRsTuVwXyZ..."
```

### Endpoints

#### Sales

```bash
# List sales for shop
GET /api/sales
Headers: Authorization: Bearer {token}
Query: ?shop_id=1&limit=50

# Create sale (offline-first sync)
POST /api/sales
Headers: 
  - Authorization: Bearer {token}
  - Content-Type: application/json
Body: {
  "shop_id": 1,
  "customer_id": 5,
  "sale_lines": [
    {
      "product_id": 10,
      "quantity": 2,
      "unit_price": 100.00
    }
  ],
  "payment_method": "cash",
  "total": 200.00,
  "created_at": "2024-01-15T14:30:00Z"
}

# Record payment for sale
POST /api/sales/{sale_id}/payments
Headers: Authorization: Bearer {token}
Body: {
  "amount": 200.00,
  "method": "cash",
  "reference": "CASH001"
}
```

#### Products

```bash
# List products with stock for shop
GET /api/products?shop_id=1
Response: [
  {
    "id": 1,
    "name": "Widget A",
    "sku": "WA-001",
    "price": 100.00,
    "shop_stock": 45,
    "warehouse_stock": 200
  }
]

# Get product details
GET /api/products/{product_id}
Response: {
  "id": 1,
  "name": "Widget A",
  "sku": "WA-001",
  "description": "Blue widget for sale",
  "price": 100.00,
  "category": "Widgets",
  "unit": "piece",
  "stock_levels": {
    "warehouse": 200,
    "shop_1": 45,
    "shop_2": 32
  }
}
```

#### Customers

```bash
# List customers
GET /api/customers?shop_id=1

# Create customer
POST /api/customers
Body: {
  "name": "ABC Corp",
  "contact_person": "John Manager",
  "phone": "+966501234567",
  "email": "john@abc.com",
  "address": "123 Main St, Riyadh"
}

# Get customer AR
GET /api/customers/{customer_id}/receivables
Response: {
  "total_due": 15000,
  "current": 10000,
  "30-60_days": 5000,
  "60-90_days": 0,
  "over_90_days": 0,
  "invoices": [...]
}
```

---

## 🐳 Docker Deployment

### Local Development

```bash
# Start all services (builds if needed)
docker compose up -d

# View logs
docker compose logs -f app

# Stop services
docker compose down

# View running containers
docker compose ps
```

### Production Deployment

```bash
# Use production compose file
docker compose -f docker-compose.prod.yml up -d --build

# This profile:
# - Doesn't expose DB/Redis to host
# - Binds web only to 127.0.0.1
# - Expects reverse proxy (nginx, Caddy) on port 80/443
# - Better security posture
```

### Environment Variables

```bash
# Application
APP_NAME="SyntekPro ERP"
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:xxx...
APP_URL=https://erp.company.com

# Database
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=syntekpro
DB_USERNAME=user
DB_PASSWORD=strong_password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=password

# ZATCA (Saudi VAT)
ZATCA_SELLER_LEGAL_NAME="Company Legal Name"
ZATCA_SELLER_VAT_NUMBER="311111111111111"
```

### Backup & Restore

```bash
# Backup database
docker compose exec db mysqldump -u syntekpro -p syntekpro > backup.sql

# Restore database
docker compose exec -T db mysql -u syntekpro -p syntekpro < backup.sql

# Backup persistent volumes
docker run --rm -v syntekpro-db:/data -v $(pwd):/backup \
  ubuntu tar czf /backup/db-backup.tar.gz -C /data .
```

---

## 🐛 Troubleshooting

### Common Issues

#### Issue: "Connection refused" on port 8080

**Solution:**
```bash
# Check if container is running
docker compose ps web

# If not running, check logs
docker compose logs web

# Restart services
docker compose down
docker compose up -d

# Verify port is accessible
curl http://localhost:8080
```

#### Issue: "SQLSTATE[HY000] [2002] Connection refused"

**Solution:**
```bash
# Database container may not be ready
docker compose down
docker compose up -d --wait db

# Wait for DB readiness
docker compose exec db mysqladmin ping -u root -p root

# Then start app
docker compose up -d app
```

#### Issue: "Allowed memory exhausted"

**Solution:**
```bash
# Increase PHP memory limit in Dockerfile
# Or run command with more memory
docker compose exec -e MEMORY_LIMIT=512M app php artisan command

# Check current usage
docker stats
```

#### Issue: "CORS or 419 CSRF token mismatch" on API calls

**Solution:**
```bash
# Verify token in Authorization header
# Make sure session cookie is sent with requests
curl -X POST http://localhost:8080/api/endpoint \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Issue: Demo mode stuck or won't reset

**Solution:**
```bash
# Manually trigger reset (use carefully!)
docker compose exec app php artisan demo:reset

# Check database name contains 'demo'
docker compose exec db mysql -u root -p root -e "SHOW DATABASES LIKE '%demo%';"
```

#### Issue: Email not sending in production

**Solution:**
```bash
# Verify mail credentials in .env
# Test mail sending
docker compose exec app php artisan tinker
>>> Mail::raw('Test', function($message) { 
    $message->to('test@example.com'); 
});

# Check mail logs
docker compose exec app tail -f storage/logs/laravel.log | grep -i mail
```

---

## 👨‍💻 Development

### Useful Commands

```bash
# Run tests
docker compose exec app php artisan test

# Run specific test
docker compose exec app php artisan test --filter=ShopTest

# Generate fake data
docker compose exec app php artisan tinker
>>> factory(App\Models\Sale::class, 100)->create()

# Clear caches
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear

# Make new model/controller
docker compose exec app php artisan make:model Sale --controller --migration

# Watch frontend for changes
docker compose run --rm --service-ports node run dev -- --host 0.0.0.0

# Format code
docker compose exec app php artisan pint

# Check code issues
docker compose exec app php artisan pint --test
```

### Project Structure

```
SyntekPro-ERP/
├── app/
│   ├── Console/           # CLI commands
│   ├── Exceptions/        # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/   # Request handlers
│   │   ├── Requests/      # Form validation
│   │   └── Middleware/    # HTTP middleware
│   ├── Models/            # Eloquent models
│   ├── Services/          # Business logic
│   ├── Policies/          # Authorization
│   └── Providers/         # Service providers
├── database/
│   ├── migrations/        # Schema definitions
│   ├── seeders/           # Test data
│   └── factories/         # Model factories
├── resources/
│   ├── views/             # Blade templates
│   └── css/               # Tailwind styles
├── routes/
│   ├── web.php            # Web routes
│   └── api.php            # API routes
├── tests/
│   ├── Feature/           # Feature tests
│   └── Unit/              # Unit tests
├── storage/
│   ├── app/               # File uploads
│   └── logs/              # App logs
├── docker/
│   └── nginx/             # Nginx config
├── Dockerfile             # Container definition
├── docker-compose.yml     # Local dev compose
├── docker-compose.prod.yml # Production compose
├── .env.example           # Environment template
├── composer.json          # PHP dependencies
├── package.json           # Node dependencies
└── README.md              # This file
```

### Contributing

1. Create feature branch: `git checkout -b feature/my-feature`
2. Make changes with tests
3. Format code: `php artisan pint`
4. Run tests: `php artisan test`
5. Commit: `git commit -m "feat: add my feature"`
6. Push: `git push origin feature/my-feature`
7. Create Pull Request

---

## 📞 Support & Contact

- **Documentation**: See `docs/` folder for detailed guides
- **Issues**: Report bugs via issue tracker
- **Community**: Join our Slack channel
- **Commercial Support**: contact@syntekpro.com

---

## 📜 License

This project is licensed under the MIT License - see LICENSE file for details.

---

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com)
- UI powered by [Livewire](https://livewire.laravel.com) and [Tailwind CSS](https://tailwindcss.com)
- Icons by [Lucide Icons](https://lucide.dev)
- ZATCA compliance support from official Saudi VAT documentation

---

**Last Updated:** January 2024 | **Version:** 1.0.0
