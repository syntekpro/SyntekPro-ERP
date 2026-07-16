# Phase 3 POS PWA Contract

This document fixes the payload shape shared by the cashier UI and the sync endpoint.

## Sale payload

A sale is sent as a single JSON object with this structure:

```json
{
  "idempotency_key": "a2c9e0e4-9d4f-4f1a-b3d6-0f4b5f4a7d7c",
  "shop_id": 1,
  "cashier_id": 12,
  "sold_at": "2026-07-16T10:32:00Z",
  "subtotal": "120.00",
  "vat_total": "18.00",
  "total": "138.00",
  "items": [
    {
      "product_id": 44,
      "product_name": "Premium Rice 5kg",
      "sku": "RICE-5KG",
      "barcode": "6291234567890",
      "quantity": "2.000",
      "unit_price": "50.00",
      "vat_rate": "7.50",
      "vat_amount": "7.50",
      "line_total": "107.50"
    }
  ]
}
```

### Required fields

- `idempotency_key`: unique per sale attempt. The sync endpoint treats the combination of `shop_id` and `idempotency_key` as the dedupe key.
- `shop_id`: the shop that owns the sale.
- `cashier_id`: the authenticated cashier responsible for the sale.
- `sold_at`: ISO-8601 timestamp captured on the device when the sale is completed.
- `items`: non-empty list of sale lines.
- Each item must carry `product_id`, `quantity`, `unit_price`, `vat_rate`, `vat_amount`, and `line_total`.
- `unit_price` is the pre-VAT selling price for one unit.
- `vat_amount` is calculated from the line subtotal and `vat_rate`, then folded into `line_total`.

### Sync request envelope

Queued offline sales are posted as:

```json
{
  "sales": [
    { "...sale payload..." }
  ]
}
```

### Sync response envelope

The API returns one result per sale:

```json
{
  "results": [
    {
      "idempotency_key": "a2c9e0e4-9d4f-4f1a-b3d6-0f4b5f4a7d7c",
      "status": "synced",
      "sale_id": 101
    }
  ]
}
```

Possible statuses are `synced`, `duplicate`, and `rejected`.

### Conflict policy

- If the same `shop_id` and `idempotency_key` arrive again with the same payload hash, the server returns `duplicate` and performs no stock mutation.
- If the same `shop_id` and `idempotency_key` arrive again with a different payload hash, the server rejects the request as a conflict.
- If the server discovers insufficient `shop_stock` during sync, the whole sale is rejected and no partial stock update is applied.
- This repo intentionally chooses whole-sale rejection on stock conflicts so the cashier can resolve the discrepancy explicitly instead of silently splitting a sale.

### Local store assumption

The POS shell caches product catalog and shop stock in IndexedDB. The offline screen uses that local snapshot to validate cart operations before the sale is queued.
