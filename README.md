# SyntekPro ERP

SyntekPro ERP is a modern, enterprise-grade multi-tenant ERP and Point of Sale (POS) platform engineered for the Saudi market. The system uses a shared-database tenancy architecture with row-level shop scoping for localized operations, alongside centralized hub governance for multi-branch retail, wholesale, and distribution chains.

---

## 📚 Complete Documentation Suite

- **[START_HERE.md](START_HERE.md)** — Getting started index, role-based onboarding paths, and daily operational cheat-sheets.
- **[QUICK_REFERENCE.md](QUICK_REFERENCE.md)** — Business cycle flowcharts, complete navigation tree, shortcut keys, and troubleshooting tables.
- **[TUTORIAL_GUIDE.md](TUTORIAL_GUIDE.md)** — 15 hands-on, step-by-step tutorials covering all operational, financial, and compliance workflows.
- **[README_COMPLETE.md](README_COMPLETE.md)** — Comprehensive architecture, API endpoints, schema diagrams, and deployment manual.
- **[docs/](docs/)** — Detailed architectural specifications, phase documentation (Phases 0–16), and design system guides.

---

## 🚀 Key Platform Capabilities

SyntekPro ERP delivers an end-to-end business management lifecycle across 16 core milestone phases:

- **Phase 0 — Foundation & Tenancy**: Multi-tenant architecture with `shop_id` scoping, Sanctum API authentication, Dockerized micro-services, and automated policy boundaries.
- **Phase 1 — Hub Master Data**: Centralized CRUD for Shops, Warehouses, Products, and Users with soft-deactivation preserving audit trails.
- **Phase 2 — Stock Operations**: Multi-location stock tracking (`warehouse_stock` vs `shop_stock`), transfer dispatch/receive workflows with row-level allocation locking and transfer-time reservation checks.
- **Phase 3 — Point of Sale (POS)**: Offline-first Progressive Web App (PWA) with IndexedDB queue, service worker caching, barcode scanner support, and idempotent sales synchronization.
- **Phase 4 — Operational Reporting**: Real-time sales summaries, VAT reporting, profit margin analysis, and fast-moving inventory tracking.
- **Phase 5 — Hardening & Demo Environment**: Automated nightly demo reset scheduler (`php artisan demo:reset`), database name safety guards, and isolated production reverse-proxy network profiles.
- **Phase 6 — General Ledger Accounting**: Full double-entry Chart of Accounts, manual journal entries, automated transaction posting in integer cents, and real-time Trial Balance verification.
- **Phase 7 — Purchasing & Accounts Payable**: Supplier records, purchase orders (draft → submitted → partially received → received → closed), automated 3-way match, supplier bills, and AP Aging reports.
- **Phase 8 — Sales & Accounts Receivable**: Customer credit management, credit-limit enforcement, credit sale receivables, customer payment allocations, and AR Aging reports.
- **Phase 9 — Financial Statements & Period Close**: Balance Sheet with balance validation, Income Statement (P&L company-wide or per-shop), indirect Cash Flow Statement, and monthly Fiscal Period lock/reopen controls.
- **Phase 10 — Returns & Refunds**: Credit Notes for customer sales returns and Debit Notes for supplier purchase returns, with atomic stock restock and GL accounting reversals.
- **Phase 11 — Settings, Dynamic Roles & Branding**: Livewire-powered settings hub, custom role creation with granular permission assignment, logo & touch icon branding, and theme customization.
- **Phase 12 — Units of Measurement & Pricing Tiers**: Flexible units (Pieces, Boxes, Kilograms, Cartons) and multi-tier pricing categories (Retail, Wholesale, VIP, Contractor).
- **Phase 13 — Modern Enterprise Design System**: Streamlined UI with flatter cards, header live clock, customizable hover Quick Menu, Dark/Light/Auto themes, Command Palette (`Ctrl+K`), Drawer collapse (`Ctrl+B`), and persistent sidebar scroll position.
- **Phase 14 — Catalog Transfer & Document Sharing**: Bulk CSV product import with column mapping preview/confirmation, product catalog export, document printing, email dispatch, and secure token-based public sharing with expiration & revocation.
- **Phase 15 — Cheques Register**: Comprehensive Post-Dated Cheque (PDC) lifecycle (Received/Issued → Deposited → Cleared / Bounced) with automatic ledger journal entries.
- **Phase 16 — Bilingual Localization**: Complete Arabic (RTL) and English (LTR) language support with high-legibility `IBM Plex Sans Arabic` typography.
- **Bank Reconciliation**: Bank accounts management, statement CSV upload, automatic matching, discrepancy auditing, and reconciliation sign-off.
- **ZATCA Phase 2 Compliance**: Saudi e-invoicing Phase 2 onboarding, CSR generation, cryptographic private key download, and Phase 1 TLV QR code encoding.

---

## 🧭 Hub Navigation Architecture

The main back office navigation is organized into 6 core functional modules:

```text
├── 1. Operations
│   ├── Shops (Branch management)
│   ├── Warehouses (Central stock-holding hubs)
│   ├── Products (Central catalog, pricing, barcodes, CSV import/export)
│   └── Stock Transfers (Inter-location transfer & receiving)
├── 2. Purchasing
│   ├── Suppliers (Vendor directory & terms)
│   ├── Purchase Orders (Procurement orders & receiving)
│   ├── Supplier Bills (AP bills & payments)
│   └── Debit Notes (Purchase returns & vendor debit memos)
├── 3. Sales
│   ├── Customers (Client records & credit limits)
│   ├── Customer Receivables (AR tracking & collection receipts)
│   ├── Credit Notes (Sales returns & customer refunds)
│   └── POS (Terminal sales & cashier checkout)
├── 4. Accounting
│   ├── Accounts (Double-entry Chart of Accounts)
│   ├── Journal Entries (Manual adjusting GL entries)
│   ├── Cheques Register (PDC issuance, clearing & bounce tracking)
│   ├── Fiscal Periods (Monthly period lock & reopen controls)
│   ├── Bank Accounts (Treasury & bank master records)
│   └── Bank Reconciliation (Statement import & ledger matching)
├── 5. Reports
│   ├── Reports Overview (Analytics & executive dashboard)
│   ├── Trial Balance (Debits/Credits equality verification)
│   ├── Balance Sheet (Assets, Liabilities & Equity statement)
│   ├── Income Statement (P&L with revenue, COGS & gross profit)
│   ├── Cash Flow (Operating, investing & financing activities)
│   ├── AP Aging (Payables aging by overdue buckets)
│   └── AR Aging (Receivables aging by overdue buckets)
└── 6. Administration
    ├── Users (Staff accounts, branch assignment & role assignment)
    ├── Units (Units of measure: pcs, kg, box, etc.)
    ├── Price Categories (Retail, Wholesale, VIP, Contractor tiers)
    ├── Product Categories (Taxonomy & classification trees)
    ├── Brands (Manufacturer & trademark directories)
    ├── Settings / Roles / Branding (System preferences, permissions, logos)
    └── ZATCA Compliance (E-invoicing certificates, CSR & keys)
```

---

## ⚡ Productivity Features & Shortcuts

- **Command Palette (`Ctrl+K` / `Cmd+K`)**: Jump instantly to any screen, report, or configuration page.
- **Drawer Collapse (`Ctrl+B` / `Cmd+B`)**: Collapse or expand the sidebar navigation for maximum workspace.
- **Sidebar Scroll Memory**: Retains exact scroll position on clicks and page loads so you never lose your place.
- **Quick Menu**: Top bar hover-intent quick-tile launcher with customizable visible shortcuts.
- **Theme Switcher**: Instant toggle between Light Mode, Dark Mode, and System Preference.
- **Bilingual Toggle**: Seamless one-click switch between English and Arabic (RTL).

---

## 🛠️ Technology Stack

- **Backend**: PHP 8.2+, Laravel 12
- **Frontend**: Blade, Livewire 3, Tailwind CSS v4, Vanilla JavaScript, Vite
- **Database**: MariaDB / MySQL 8.0+
- **Cache & Queue**: Redis
- **Offline Client**: IndexedDB, Service Workers (PWA)
- **Deployment**: Docker Compose, Nginx, PHP-FPM

---

## 🚀 Local Docker Setup

1. **Clone the repository and copy the environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Start the core Docker services:**
   ```bash
   docker compose up -d --build app web db redis
   ```

3. **Install PHP and Node dependencies:**
   ```bash
   docker compose run --rm composer install
   docker compose run --rm node install
   ```

4. **Generate application key and run migrations with seed data:**
   ```bash
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --seed
   ```

5. **Compile frontend assets:**
   ```bash
   docker compose run --rm node run build
   ```

6. **Access the application:**
   - Web Hub: [http://localhost:8080](http://localhost:8080)
   - Seeded Super Admin: `development@example.com`
   - Default Password: `password`

---

## 🧪 Testing & Quality Assurance

Run the automated test suite:
```bash
docker compose exec app php artisan test
```

Run focused test suites by module:
```bash
# Tenancy & Auth
docker compose exec app php artisan test --filter='(ShopTenancyTest|ApiTokenTest)'

# Accounting & Financial Statements
docker compose exec app php artisan test --filter='(AccountingEngineTest|FinancialReportsTest)'

# Inventory, Purchasing & Sales
docker compose exec app php artisan test --filter='(StockSchemaTest|PurchaseOrderTest|CustomerReceivableTest)'
```

---

## 🌐 Production Deployment Notes

- Use `docker-compose.prod.yml` for isolated production deployments.
- Database and Redis instances are kept off host ports and communicate via private Docker networks.
- Web traffic is bound strictly to `127.0.0.1:${WEB_HTTP_PORT}:80` for reverse-proxy termination (Nginx, Traefik, or Caddy) with SSL certificates.
- Multi-stack deployments require unique `COMPOSE_PROJECT_NAME` and `WEB_HTTP_PORT` environment definitions.

---

## 🏷️ Releasing a New ERP Version

SyntekPro ERP versions are published as GitHub Releases and versioned Docker images on GitHub Container Registry (GHCR).

### 1. Prepare the release

Ensure `config/syntek.php` contains the target version string, or export it via `.env`:

```bash
SYNTEK_VERSION=1.0.1
```

### 2. Tag the release

Use semantic versioning with a `v` prefix:

```bash
git checkout main
git pull origin main
git tag -a v1.0.1 -m "Release SyntekPro ERP v1.0.1"
git push origin v1.0.1
```

Only tags matching `v[0-9]+.[0-9]+.[0-9]+*` trigger the release workflow.

### 3. Create a GitHub Release (optional but recommended)

- Open the repository on GitHub.
- Go to **Releases → Draft a new release**.
- Choose the tag you just pushed, e.g. `v1.0.1`.
- Fill in the release title and notes, then **Publish release**.

Publishing a release also creates the tag, so either pushing the tag or publishing the release starts the workflow.

### 4. Verify the Docker image

The [Release Docker Image](.github/workflows/release.yml) workflow builds and pushes:

```
ghcr.io/syntekpro/syntekpro-erp:1.0.1
ghcr.io/syntekpro/syntekpro-erp:latest
```

You can inspect the published image with:

```bash
docker pull ghcr.io/syntekpro/syntekpro-erp:1.0.1
docker image inspect ghcr.io/syntekpro/syntekpro-erp:1.0.1 \
  --format '{{ index .Config.Labels "org.opencontainers.image.version" }}'
```

### Required GitHub settings

- **Actions permissions**: Allow GitHub Actions to create and approve pull requests is not required.
- **Packages permissions**: The workflow uses `GITHUB_TOKEN`, which is granted `packages: write` automatically for workflows in this repository. No personal access token is needed.
- **Workflow permissions**: Ensure the default `GITHUB_TOKEN` has at least **Read and write permissions** enabled under **Settings → Actions → General → Workflow permissions**.

## 📜 License

SyntekPro ERP is proprietary software. All rights reserved.
