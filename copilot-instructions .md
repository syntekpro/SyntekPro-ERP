# SyntekPro ERP — Copilot Project Instructions (v1.0.0)

## Project

Build **SyntekPro ERP** — a multi-tenant, chain-shop ERP/POS platform for the
Saudi market. This is a brand-new codebase, built from scratch.

Start at **version 1.0.0** (semver). Tag releases properly from the first
commit onward (`composer.json` version field = `1.0.0`).

## Deployment targets

- **Production hub**: `erp.syntekpro.com` — the central ERP admin hub
- **Shop POS terminals**: path-based under the same domain —
  `erp.syntekpro.com/shop1`, `/shop2`, etc.
- **Free demo**: `demo.erp.syntekpro.com` — a separate subdomain (not a path)
  so demo sessions/cookies are naturally isolated from production. Seeded
  fictional shop(s) and products, reset on a schedule (e.g. nightly), clearly
  labeled "Demo Mode" in the UI, and completely unable to touch real
  tenant/shop data.
- **Marketing/main site**: `syntekpro.com` (separate property — ERP links
  back to it, e.g. footer/login page, but it's not part of this build).
- **Admin/contact email**: `development@syntekpro.com` — use as the default
  seeded super-admin account and as the "from" address for system
  notifications until a dedicated mail identity is configured.
- **Deployment method**: **Docker**, self-hosted on our own server. Build the
  app assuming containerized deployment from day one:
  - App container (PHP-FPM + Laravel), a web server container (nginx) in
    front of it, a MySQL/MariaDB container, and a Redis container if Redis is
    the chosen queue/cache driver.
  - All config via `.env` / environment variables — no hardcoded paths, no
    assumptions of a specific host filesystem layout, nothing that assumes
    Apache/XAMPP.
  - Provide a `docker-compose.yml` for local dev that mirrors production
    shape as closely as practical, plus a production-oriented Dockerfile
    (multi-stage build: install deps, build frontend assets, produce a lean
    runtime image).
  - Reverse proxy / SSL termination (e.g. via a shared nginx-proxy or
    Caddy in front of the containers) is assumed to be handled at the host
    level, outside this repo — but make sure `APP_URL`, trusted proxies, and
    Sanctum's stateful domains are configured correctly for that setup.

## Branding assets

Logo and icon files will be supplied separately (already designed). When
scaffolding the frontend and PWA manifest:
- Reserve `public/images/logo.svg` (or `.png`) for the main logo and
  `public/images/icon-*.png` (multiple sizes: 192x192, 512x512 at minimum)
  for app/PWA icons and favicon.
- Wire these into the PWA `manifest.json`, favicon links, and the hub
  admin's header/login screen as placeholders now, so dropping the real
  files in later requires no code changes — just replacing the files.

## Stack decision

Core constraints are locked; everything else is yours to choose and justify
briefly before scaffolding:

**Locked:**
- Backend framework: **Laravel** (latest stable LTS), PHP 8.2+
- Database: **MySQL/MariaDB**, single shared database (no per-tenant DBs)
- Multi-tenancy: shared-DB, `shop_id`-scoped Eloquent global scopes — not
  package-based tenancy, not database-per-tenant
- Auth: **Laravel Sanctum**
- Shop POS frontend must be an **offline-first PWA** (IndexedDB + service
  worker + idempotency-keyed sync engine)
- Deployment: **Docker** (see above)

**Your call — pick and state your choice up front:**
- Hub/admin frontend approach: Blade + Livewire, or Inertia.js + React/Vue.
  Pick whichever gives the cleanest offline-PWA story for the POS side too
  (e.g. if you pick Inertia + React for the hub, the POS PWA can share
  components/patterns).
- CSS framework (Tailwind is a safe default, but your call).
- Queue/cache driver (Redis vs database driver) — Redis preferred since it's
  already part of the container stack option above.
- Testing framework (Pest vs PHPUnit) and CI setup.
- Package choices for PDF/QR generation (ZATCA invoice QR), barcode
  scanning, etc.

State your stack choices and reasoning in a short `ARCHITECTURE.md` before
scaffolding Phase 0, so decisions are documented and revisitable.

## Locked architectural decisions (do not deviate without asking)

1. **Topology — hub-and-spoke**: central ERP hub at `erp.syntekpro.com`
   (root), shop POS terminals at path-based routes under it (`/shop1`,
   `/shop2`, ...). Demo is a separate subdomain (`demo.erp.syntekpro.com`),
   not part of the `/shopN` path scheme.
2. **Tenancy = shop_id scoping**: shop-owned models get a global Eloquent
   scope filtering by `shop_id`, with an explicit escape hatch for hub-level
   cross-shop queries. Product catalog is hub-owned and shared across shops
   (no `shop_id` on `products`).
3. **Stock ownership**: each shop owns its **own local stock**, transferred
   in from the central warehouse — not a shared pool. A sale in Shop A only
   decrements Shop A's local stock. Model as `warehouse_stock` (central) and
   `shop_stock` (per-shop), linked by a stock-transfer workflow.
4. **POS is offline-first**: cashier terminal works offline via IndexedDB +
   service worker; sales queue locally and sync via an idempotency-keyed API
   so reconnecting never double-counts or loses a sale.
5. **Saudi market compliance**: SAR currency, 15% VAT default, ZATCA
   e-invoicing-ready (Phase 1 QR code now, structured to extend to Phase 2
   XML/API integration later).

## Six-phase build plan

- **Phase 0 — Foundation & tenancy**: fresh Laravel install, Docker setup
  (Dockerfile + docker-compose.yml), chosen frontend stack wired up,
  `shop_id` scoping trait + global scope, `ResolveShopContext` middleware,
  core tables (`shops`, `warehouses`, `users` with nullable `shop_id`,
  `products`), Sanctum auth, roles (`super_admin`, `shop_manager`,
  `cashier`), seed the `development@syntekpro.com` super-admin, placeholder
  logo/icon wiring.
- **Phase 1 — Central ERP core**: hub admin UI/API for products, pricing,
  warehouses, users, shop management, dashboards/KPIs.
- **Phase 2 — Stock transfers**: warehouse → shop transfer workflow, transfer
  orders, receiving/confirmation at shop side, `shop_stock` updates, audit
  trail of stock movements.
- **Phase 3 — POS PWA & sync engine**: offline-capable cashier terminal,
  IndexedDB local queue, service worker, idempotency-keyed sync API, conflict
  handling for stock-decrement races.
- **Phase 4 — Reporting & ZATCA compliance**: VAT report, margin report,
  fast-moving SKU report, ZATCA QR invoice generation, groundwork for Phase 2
  XML/API e-invoicing.
- **Phase 5 — Demo environment & hardening**: build the
  `demo.erp.syntekpro.com` seeded environment + reset job, rate limiting,
  audit trails, backup/restore, permissions matrix refinement, load/perf
  pass, tag `v1.0.0` release.

Work phase by phase, in order. Don't build later-phase features early unless
asked — flag it if a request seems to jump ahead.

## Coding conventions

- Standard Laravel conventions: Eloquent models + relationships, Form
  Requests for validation, Policies for authorization, API Resources for
  JSON shaping, migrations for every schema change.
- Money fields as `decimal(12,2)`, never floats.
- Every migration/model touching shop-scoped data uses the `BelongsToShop`
  trait (create in Phase 0, reuse throughout).
- Write feature tests for stock transfers and POS sale flows especially —
  highest risk for double-counting or lost stock/sales.
- Ask before schema changes spanning more than one phase's tables, or before
  adding a new package/dependency not already implied above.
- Keep `ARCHITECTURE.md` updated as decisions are made or revised.
- Keep Docker in mind for every config decision — no local-filesystem
  assumptions, no host-specific paths, everything via `.env`.

## What NOT to do

- Don't build multi-database or per-tenant-schema tenancy — single shared DB
  with `shop_id` scoping only.
- Don't use a flat per-tenant stock quantity — stock is always
  warehouse-scoped or shop-scoped.
- Don't let the demo subdomain share sessions, cookies, or data with real
  shops.
- Don't implement offline sync, ZATCA, or reporting before Phase 0–2 are
  solid.
