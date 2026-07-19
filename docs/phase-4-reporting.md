# Phase 4 Reporting and ZATCA Notes

## Reporting Data Source Decision

### Requirement
Hub-level filterable reports by shop and date range:
- VAT report
- Margin report
- Fast-moving SKU report

### Reasoning
The first implementation uses direct queries on `sales` and `sale_items` because:
- Existing sync already writes normalized transactional data with shop and sold timestamp, which are the required filter dimensions.
- The expected scope is operational reporting for current ERP usage, not heavy multi-year BI workloads.
- Avoids introducing write-time complexity and reconciliation drift during Phase 4.
- Keeps report correctness tied to source-of-truth transactions while domain behavior is still stabilizing.

### Decision
For Phase 4, reports query `sales` and `sale_items` directly.

### When to introduce summarized/materialized tables
Add a summarized table (or materialized view equivalent) when one or more of these conditions appears in production:
- Report queries exceed acceptable response times under realistic concurrent hub usage.
- Data volume growth causes repeated aggregate scans to become a cost issue.
- Business requests fixed-period snapshots that must not change with late data corrections.

## Implemented Report Coverage

The reports page computes:
- VAT: total VAT, gross totals, sale count grouped by shop.
- Margin: revenue (ex VAT), COGS, and margin grouped by shop and product.
- Fast-moving SKU: quantity sold and sales value grouped by shop and product.

All reports support:
- Optional date range (`start_date`, `end_date`)
- Optional shop filter for super admins
- Shop-scoped filtering for shop managers

## ZATCA QR Groundwork

On each synced sale, the backend now stores:
- `sales.zatca_qr_payload`: TLV-encoded and Base64 string payload
- `sales.invoice_uuid`: invoice UUID placeholder for Phase 2 continuity
- `sales.invoice_hash`: SHA-256 hash placeholder for invoice chaining groundwork

TLV payload currently includes:
- Tag 1: seller legal name
- Tag 2: seller VAT registration number
- Tag 3: sale timestamp
- Tag 4: invoice total
- Tag 5: VAT total

## ZATCA Phase 2 Groundwork Explicitly Added

This phase does not implement full e-invoicing integration/signing.
It prepares the data model and persistence needed for later Phase 2 work:
- Invoice UUID field
- Invoice hash field
- Persisted QR payload field on sale

Future Phase 2 implementation should add:
- Cryptographic signing and certificate lifecycle
- Cryptographic stamp and XML/UBL generation as required
- Full invoice hash-chain rules and submission/clearance integration

## Seller Identity Configuration (Needs Product Decision)

Superseded in Phase 11: seller legal name and VAT number now live in the singleton `business_settings` table and are edited through Hub Settings. The old env fallback below is retained only as historical Phase 4 context.

Implemented lookup order:
1. Per-shop values (`shops.legal_name`, `shops.vat_registration_number`)
2. Fallback global values (`ZATCA_SELLER_LEGAL_NAME`, `ZATCA_SELLER_VAT_NUMBER`)

This supports both single-company and multi-entity deployments.

Open decision for product owner:
- Should seller legal name and VAT number be enforced per shop only,
  or centrally company-wide only,
  or keep the current per-shop with global fallback strategy?
