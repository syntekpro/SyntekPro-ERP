# SyntekPro ERP

SyntekPro ERP is a Laravel-based multi-tenant ERP and POS platform for the Saudi market. The application uses a shared database tenancy model where shop-owned data is isolated with `shop_id` scoping, while hub-owned resources such as shops, warehouses, and products remain unscoped and centrally managed.

## Product Documentation

- Full Feature List (Phase 0-9): [docs/syntekpro-erp-feature-list.md](docs/syntekpro-erp-feature-list.md)
- End-User Operating Guide: [docs/syntekpro-erp-user-guide.md](docs/syntekpro-erp-user-guide.md)

These two documents provide the business-facing product description and operational usage details for the current `v1.0.0` milestone.

## Current Phase

This repository currently includes:

- Phase 0 foundation: tenancy primitives, Sanctum auth, Docker-first configuration, roles, and baseline feature tests.
- Phase 1 hub UI: dashboard metrics plus CRUD surfaces for shops, warehouses, products, and users (with active/inactive lifecycle controls).
- Phase 2 stock operations: transfer creation, dispatch, receive workflow, central and local stock movement tracking, plus transfer-time reservation warnings.
- Phase 3 POS runtime: shop cashier POS shell and idempotent sale sync API with local stock decrement behavior.
- Phase 4 reporting and compliance groundwork: VAT/margin/fast-moving reports and persisted ZATCA TLV Base64 QR payload, invoice UUID, and invoice hash fields.
- Phase 5 hardening (in progress): demo-mode reset command and nightly scheduler hook, demo safety guard, demo banner, and production compose profile for reverse-proxy networking.
- Phase 6 accounting core: chart of accounts, manual journal entries, auto-balancing validations, and trial balance.
- Phase 7 purchasing and AP: supplier management, purchase orders, receiving, supplier bills/payments, and AP aging.
- Phase 8 receivables: customers, credit sales, customer payments, and AR aging.
- Phase 9 financial statements and close: balance sheet, income statement, cash flow, and fiscal period close/reopen controls.

### Demo Environment Notes

- `APP_DEMO_MODE=true` enables demo safeguards and nightly reset scheduling.
- Demo reset command: `php artisan demo:reset`
- Scheduler trigger (demo mode only): daily at `DEMO_RESET_TIME` (default `03:00`).
- Safety guard: demo mode refuses to boot/reset unless the active database name contains `demo`.
- Use dedicated demo infrastructure and credentials only; do not share production database, redis instance, or session cookie domain.

### Production Compose Hardening

- Use `docker-compose.prod.yml` for production-style topology.
- Database and Redis are no longer published to host ports in that profile.
- The web service is published only on loopback for reverse-proxy handoff:
  `127.0.0.1:${WEB_HTTP_PORT}:80` (never `0.0.0.0`)
- `WEB_HTTP_PORT` must be unique per stack on the same host.
  Example production: `WEB_HTTP_PORT=8082`; example demo: `WEB_HTTP_PORT=8083`.
- Set a unique `COMPOSE_PROJECT_NAME` per stack to prevent cross-stack resource name collisions.
  Example production: `COMPOSE_PROJECT_NAME=syntekpro-prod`; example demo: `COMPOSE_PROJECT_NAME=syntekpro-demo`.
- The web service also joins external `syntekpro-net` for existing Docker-network-based integrations.

## Stack

- PHP 8.2+
- Laravel 12
- Blade + Livewire 3
- MariaDB / MySQL
- Redis for queue and cache
- Vite + Tailwind CSS
- Docker Compose for local development

## Local Docker Setup

1. Copy the environment file.

```bash
cp .env.example .env
```

2. Build and start the core containers.

```bash
docker compose up -d --build app web db redis
```

3. Install PHP dependencies.

```bash
docker compose run --rm composer install
```

4. Install frontend dependencies.

```bash
docker compose run --rm node install
```

5. Generate the application key and run migrations.

```bash
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

6. Build frontend assets for the hub UI.

```bash
docker compose run --rm node run build
```

7. Open the application.

```text
http://localhost:8080
```

The default seeded super-admin account is controlled by these environment values:

- `SEED_SUPER_ADMIN_EMAIL=development@syntekpro.com`
- `SEED_SUPER_ADMIN_PASSWORD=password`

## Useful Commands

Run the full test suite:

```bash
docker compose exec app php artisan test
```

Run focused feature slices:

```bash
docker compose exec app php artisan test --filter='(ShopTenancyTest|ApiTokenTest)'
docker compose exec app php artisan test --filter='(AuthorizationPolicyTest|StockSchemaTest|HubCrudTest)'
```

Run Vite in watch mode from Docker:

```bash
docker compose run --rm --service-ports node run dev -- --host 0.0.0.0
```

## Hub Modules In Progress

- Shop management
- Warehouse management
- Product catalog management
- Dashboard KPI counts
- Stock transfer schema groundwork

## Repository Notes

- Hub-owned models do not carry `shop_id`: `shops`, `warehouses`, and `products` remain central resources.
- Shop-owned records should use the `BelongsToShop` trait and inherit the global shop scope.
- The stock model is intentionally split into `warehouse_stock` and `shop_stock`; there is no shared polymorphic stock table.

## Deployment Direction

The local Docker stack mirrors the intended production shape:

- `app`: PHP-FPM Laravel container
- `web`: nginx reverse proxy for the Laravel public directory
- `db`: MariaDB
- `redis`: Redis for cache/queue

Reverse proxy SSL termination and host-level routing are expected to be handled outside this repository in production.
