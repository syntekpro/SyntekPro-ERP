# Phase 14: Product Catalog and Documents

## Products List Before/After

Before Phase 14, the Products screen was a plain card with a search input, a static table, always-visible edit/deactivate buttons, and basic pagination. It inherited some Phase 13 typography and table styling, but it did not yet use the richer toolbar/table pattern.

After Phase 14, the Products list is a catalog workbench:

- Header: Phase 13 brass/ledger identity, IBM Plex type, Lucide `plus` icon, and a primary `Create New` button.
- Toolbar: search plus real filters for status, base unit, VAT rate, and price category; `Clear`, `Export`, and `Import` actions use the shared button component.
- Table: row checkboxes, sortable Code/Name/Sales Price/Purchase Price/Base Unit headers with direction icons, status badges, figure-mono currency columns, and per-row kebab action menu.
- Bulk bar: appears when one or more rows are selected and supports bulk activate/deactivate. Bulk hard-delete is intentionally not exposed because the product module follows the existing deactivate-not-delete pattern.
- Empty state: uses the Phase 13 empty-state component for empty catalogs and empty filtered result sets.
- Pagination: remains the standard Laravel/Livewire paginator under the table.

## Product Create/Edit Before/After

Before Phase 14, create/edit was one long form: identity, tax, unit conversions, and price overrides were stacked in one uninterrupted page.

After Phase 14, create/edit is tabbed:

- Details: code/SKU, name, barcode, description, image upload with preview, active flag, and sales/purchase workflow flags.
- Pricing & Inventory: sales price, purchase price, VAT, excise applicability/rate, stock min/max thresholds, and the price category override grid.
- Units: base unit and alternate unit conversion management from Phase 12, folded into the same create/edit flow.
- Save controls: sticky footer with `Save product`, `Save & Add Another`, and `Cancel`, so saving stays visible on long tabs.

## Product Import/Export Schema

The export and import shape is intentionally the same. A CSV/XLSX export can be edited and re-imported without remapping columns.

Base columns, in order:

| Column | Meaning |
| --- | --- |
| `SKU/code` | Product SKU. Existing SKU is updated; missing SKU creates a product. |
| `name` | Product name. |
| `description` | Optional product description. |
| `base unit` | Existing unit code such as `PCS`. |
| `price` | Default sales price per base unit. |
| `purchase price` | Default purchase/cost price per base unit. |
| `VAT rate` | VAT percentage. |
| `is_excise_applicable` | Boolean: `1`, `true`, `yes`, or `active` are true. |
| `excise_rate` | Optional excise percentage. |
| `is_active` | Boolean active flag. |
| `stock_min` | Optional minimum stock threshold in base unit. |
| `stock_max` | Optional maximum stock threshold in base unit. |

Dynamic columns:

- One column per active alternate unit in the form `Unit: BOX - factor`.
- One column per active price category in the form `Price: Wholesale`.

Sample row:

```csv
SKU/code,name,description,base unit,price,purchase price,VAT rate,is_excise_applicable,excise_rate,is_active,stock_min,stock_max,Unit: BOX - factor,Price: Wholesale
WATER-1,Bottled Water,Still water,PCS,12.50,8.00,15,0,,1,5,50,12,10.00
```

Round-trip confirmation: a product with multiple alternate unit conversions and price category overrides exports every configured conversion/category into deterministic columns. Re-importing that same file validates as updates and writes the same `products`, `product_unit_conversions`, and `product_prices` data without unintended changes.

## Excel Package

Chosen package: `maatwebsite/excel`.

Reason: it is the standard Laravel package for XLSX import/export, integrates with Laravel responses and uploaded files, and avoids hand-rolling XLSX parsing. CSV remains parsed directly with PHP `fgetcsv` because it is plain text and keeps validation transparent.

## Import Validation and Confirmation

Import is an upsert by SKU/code. The upload flow is:

1. Upload CSV/XLSX.
2. Parse all rows and validate the whole file.
3. Show dry-run summary: created, updated, rejected, and per-row errors such as `row 14: unknown unit code 'DRM'` or `row 22: price must be a non-negative number`.
4. Commit only after explicit confirmation.
5. On confirmation, valid rows are committed in one transaction and invalid rows are skipped.

No row is committed during preview.

## Document Printing, Sharing, and Email

Document output applies to:

- POS sales
- Purchase orders
- Supplier bills
- Credit notes
- Debit notes

A shared printable adapter normalizes each model into:

- `document_number`
- `date`
- `counterparty_name`
- `line items`
- `subtotal`
- `VAT`
- `total`

The shared Blade print layout uses business settings branding on every document: logo, legal name, VAT number, and invoice footer text.

POS sales support two browser-print formats:

- Standard A4/Letter invoice view for email/sharing/browser PDF save.
- Narrow receipt view for 80mm browser printing.

Raw ESC/POS thermal printer protocol, printer drivers, cash-drawer pulses, and direct device integration are explicitly out of scope for Phase 14. This phase uses browser printing only.

## Share-Link Security Model

Each share request creates a new `document_shares` row:

- `token`: 64 random characters generated with Laravel `Str::random`, not a predictable document ID.
- `document_type` and `document_id`: restrict the token to exactly one document.
- `expires_at`: defaults to 30 days via `config/documents.php` and `DOCUMENT_SHARE_EXPIRY_DAYS`.
- `revoked_at`: nullable timestamp; revoked links are rejected immediately.

The unauthenticated route only accepts the token. Expired or revoked tokens return forbidden and do not render the document. Hub users can review and revoke links from the shared document links view.

## Email Model

Document email sends a secure expiring link rather than a PDF attachment. Reason: the project does not yet include a server-side PDF renderer, while the printable route already supports browser PDF save and shares the same revocation/expiry controls. The email sender uses `business_settings.mail_from_name` and `business_settings.mail_from_address`, falling back to Laravel mail config only if those settings are empty.

## Feature Tests

Phase 14 adds these focused feature tests:

- `test_importing_a_valid_file_creates_and_updates_products_with_unit_conversions_and_price_overrides`
- `test_importing_a_file_with_invalid_rows_commits_only_valid_rows_after_confirmation_and_reports_specific_errors`
- `test_export_then_reimport_of_same_catalog_produces_no_unintended_changes`
- `test_bulk_deactivate_from_products_list_deactivates_only_selected_rows`
- `test_generated_share_link_allows_unauthenticated_viewing_of_the_correct_single_document_only`
- `test_expired_or_revoked_share_link_is_rejected`
- `test_emailed_document_uses_configured_business_mail_from_name_and_address`
