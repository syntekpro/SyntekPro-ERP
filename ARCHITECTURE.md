# ERP Platform Architecture Decisions (Phase 0)

Version: 1.0.0

## Locked Constraints Honored

- Laravel 12 (PHP 8.2+)
- Shared database tenancy model with `shop_id` scoping for shop-owned data
- Sanctum for API authentication
- Docker-first deployment shape (app + nginx + mysql + redis)
- POS offline-first requirement reserved for Phase 3 (not implemented in Phase 0)

## Chosen Stack

- Hub/admin UI: Blade + Livewire
- CSS: Tailwind CSS v4
- Queue/cache: Redis (with database fallback possible via env)
- Tests: PHPUnit (Laravel default in v12 skeleton)
- CI: GitHub Actions (deferred to next phase setup)

## Why These Choices

- Blade + Livewire keeps Phase 0 implementation focused and fast while still supporting reactive screens for upcoming hub modules.
- Tailwind integrates directly with Laravel Vite tooling and keeps UI scaffolding consistent.
- Redis aligns with containerized deployment needs and future queue-heavy sync workloads.
- PHPUnit is already integrated in the fresh skeleton and sufficient for early foundation tests.

## Phase 0 Deliverables Captured

- Multi-tenant context resolution middleware (`ResolveShopContext`)
- Reusable shop scope primitive (`BelongsToShop` + global `ShopScope`)
- Core schema: `shops`, `warehouses`, `products`, and user `shop_id` + role
- Feature coverage for shop context resolution and scope escape hatch behavior
- Sanctum setup and API token endpoint (`POST /api/tokens`)
- Hub-owned models remain unscoped: `shops`, `warehouses`, and shared `products`
- Role model via enum (`super_admin`, `shop_manager`, `cashier`)
- Seeded super admin account `development@example.com`
- Dockerfile, nginx config, and docker-compose for local parity
- Branding placeholders wired in layout, favicon, and manifest

## Notes

- Product catalog is hub-owned and intentionally has no `shop_id` column.
- `BelongsToShop` is ready for all shop-owned models added in subsequent phases.
- Early Phase 2 schema uses separate `warehouse_stock` and `shop_stock` tables plus `stock_transfers` with a status lifecycle.
- Reverse proxy assumptions are reflected through env-first config and trusted proxy setup.
- Phase 3 POS contract, offline shell, and sync conflict policy are documented in `docs/phase-3-pos-pwa.md`.
