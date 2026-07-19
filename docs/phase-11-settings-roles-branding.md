# Phase 11: Business Settings, Roles & Permissions, and Branding

## Business Settings Singleton

Phase 11 adds a singleton `business_settings` row with `singleton_key = 1`. The migration seeds the row from the current `ZATCA_SELLER_LEGAL_NAME` and `ZATCA_SELLER_VAT_NUMBER` env values once at deploy time, then the Hub Settings screen becomes the source of truth for seller identity.

Runtime code now reads legal name, VAT number, VAT enablement, VAT rate, currency, formats, mail sender values, theme, logo, favicon, and invoice footer values from `business_settings`. The previous `config('zatca.*')` lookup was removed from application code.

## Permission Keys and Default Roles

The permission layer is additive over the four existing roles. `role_permissions` is seeded to match the pre-Phase-11 hardcoded policy behavior, and `user_permissions` can add a per-user `grant` or `revoke` override.

| Permission key | Super admin | Accountant | Shop manager | Cashier |
| --- | --- | --- | --- | --- |
| accounts.view | yes | yes | no | no |
| accounts.create | yes | no | no | no |
| accounts.update | yes | no | no | no |
| accounts.delete | yes | no | no | no |
| customers.view | yes | yes | no | no |
| customers.create | yes | yes | no | no |
| customers.update | yes | yes | no | no |
| credit_notes.view | yes | yes | no | no |
| credit_notes.create | yes | yes | no | no |
| debit_notes.view | yes | yes | no | no |
| debit_notes.create | yes | yes | no | no |
| journal_entries.view | yes | yes | yes | no |
| journal_entries.create | yes | yes | no | no |
| products.view | yes | yes | yes | no |
| products.create | yes | no | no | no |
| products.update | yes | no | no | no |
| products.delete | yes | no | no | no |
| purchase_orders.view | yes | yes | no | no |
| purchase_orders.create | yes | yes | no | no |
| purchase_orders.update | yes | yes | no | no |
| purchase_orders.submit | yes | yes | no | no |
| purchase_orders.receive | yes | yes | no | no |
| purchase_orders.close | yes | yes | no | no |
| settings.manage | yes | yes | no | no |
| shops.view_any | yes | no | no | no |
| shops.view | yes | no | yes | no |
| shops.create | yes | no | no | no |
| shops.update | yes | no | no | no |
| shops.delete | yes | no | no | no |
| shop_stock.view | yes | no | yes | no |
| shop_stock.update | yes | no | yes | no |
| stock_transfers.view | yes | no | yes | no |
| stock_transfers.create | yes | no | no | no |
| stock_transfers.mark_in_transit | yes | no | no | no |
| stock_transfers.receive | yes | no | yes | no |
| supplier_bills.view | yes | yes | no | no |
| supplier_bills.record_payment | yes | yes | no | no |
| suppliers.view | yes | yes | no | no |
| suppliers.create | yes | yes | no | no |
| suppliers.update | yes | yes | no | no |
| users.view | yes | no | no | no |
| users.create | yes | no | no | no |
| users.update | yes | no | no | no |
| users.delete | yes | no | no | no |
| warehouses.view | yes | yes | yes | no |
| warehouses.create | yes | no | no | no |
| warehouses.update | yes | no | no | no |
| warehouses.delete | yes | no | no | no |

## Behavior-Preserving Confirmation

The seeded map intentionally mirrors the old policy checks:

- Super admin still has every policy action except object-level constraints that already existed, such as not deleting self and not deleting an account with journal lines.
- Accountant still has accounting, AP, AR, purchasing, returns, reports-related object access, warehouse view, and product view, but not platform administration such as users, shops, product mutation, or account mutation.
- Shop manager still has product view, warehouse view, own-shop stock management, own-shop transfer receive, own-shop journal view, and own-shop view. The same shop ownership checks remain in the policy methods.
- Cashier still has no hub CRUD policy access.
- Status gates remain unchanged: purchase orders must be draft to submit, submitted or partially received to receive, received to close; stock transfers must be pending to mark in transit and in transit to receive; supplier bills must have an outstanding balance to record payment.

Regression test `test_all_default_role_permissions_match_pre_phase_11_policy_behavior` checks every existing policy method across all four default roles with those object and status conditions.

## VAT and Excise Tax

`business_settings.vat_enabled` and `business_settings.vat_rate` are now authoritative. POS sync recalculates line VAT server-side from the configured rate rather than trusting a client payload. PO receiving also uses the configured VAT rate. When VAT is disabled, VAT totals become zero and no VAT Payable or VAT Receivable journal lines are generated.

Products now support `is_excise_applicable` and nullable `excise_rate`. POS posting adds `Excise Tax Payable` account `2300` when applicable.

### Worked Excise Example

Sale of one excise-applicable item:

- Unit price: SAR 100.00
- Quantity: 1
- VAT setting: enabled at 15%
- Product excise rate: 50%

Calculated sale totals:

- Subtotal: SAR 100.00
- VAT: SAR 15.00
- Excise: SAR 50.00
- Total: SAR 165.00

Generated journal entry:

- Dr 1010 Cash on Hand: SAR 165.00
- Cr 4100 Sales Revenue: SAR 100.00
- Cr 2200 VAT Payable: SAR 15.00
- Cr 2300 Excise Tax Payable: SAR 50.00
- Dr 5100 Cost of Goods Sold: based on frozen unit cost
- Cr 1200 Inventory: based on frozen unit cost

Check before COGS lines: debits SAR 165.00, credits SAR 165.00. COGS and inventory add equal debit and credit values.

## Document Numbering

Document number prefixes now live in `document_number_formats`:

- `sales`: Sale Invoice, default `INV-`
- `credit_note`: Credit Note, default `CN-`
- `debit_note`: Debit Note, default `DN-`
- `purchase_orders`: Purchase Order, default `PO-`
- `supplier_bills`: Supplier Bill, default `BILL-`
- `stock_transfers`: Stock Transfer, default `ST-`

Reset frequency is `never`. The reason is audit continuity: these documents participate in VAT, GL, AP, AR, and returns workflows, and a forever-increasing sequence avoids duplicate-looking numbers around year/month boundaries. Date filtering and fiscal periods already provide time slicing; the document number should remain a stable identifier, not a period control.

The existing `document_counters` locking remains the concurrency control mechanism. If a format row is missing, `DocumentNumberService` falls back to the same legacy prefixes for backward compatibility.

## Branding and White Label

Branding is runtime-configured from `business_settings`; no asset rebuild is required.

Theme presets:

| Key | Name | Primary | Accent | Background | Surface |
| --- | --- | --- | --- | --- | --- |
| syntek-default | Syntek Default | `#fbbf24` | `#38bdf8` | `#0c0a09` | `#1c1917` |
| riyadh-graphite | Riyadh Graphite | `#22c55e` | `#eab308` | `#111827` | `#1f2937` |
| red-sea | Red Sea | `#06b6d4` | `#f97316` | `#082f49` | `#0f3f5f` |
| date-palm | Date Palm | `#84cc16` | `#14b8a6` | `#1a2e05` | `#2b3f12` |

Logo and favicon paths are stored on the singleton. If either file is missing or invalid at render time, the layout falls back to `/images/logo-full.png` and `/images/icon-main.png`, so branding cannot break the login screen. The PWA manifest is now generated by the `/manifest.json` route using the configured business name, theme color, and icon fallback.

A fixed, non-configurable `Powered by SyntekPro ERP` link to `https://syntekpro.com` appears in the login screen footer and hub layout footer.

## Test Coverage Added

- `test_vat_disabled_sales_post_no_vat_lines`
- `test_excise_tax_posts_correctly_alongside_vat`
- `test_all_default_role_permissions_match_pre_phase_11_policy_behavior`
- `test_user_level_permission_override_grants_and_revokes_beyond_role_default`
- `test_document_numbering_respects_configured_custom_prefix`
- `test_invalid_logo_path_falls_back_to_default_on_login_page`
- `test_settings_legal_name_and_vat_number_are_reflected_in_new_zatca_qr_codes`
