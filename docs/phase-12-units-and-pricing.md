# Phase 12: Units of Measure and Price Categories

## Scope

Phase 12 adds:

- Units of measure with one base unit per product
- Product-specific unit conversions
- Transaction-unit capture on purchasing, stock transfers, POS sales, credit notes, and debit notes
- Price categories with product-level price overrides
- Customer and shop default price categories for POS pricing

Backward compatibility is preserved by seeding `PCS` as the default unit and assigning it to every existing product and historical transaction line. A deployment that never configures alternate units or price categories keeps the same quantities, prices, stock movements, and journal entries as before Phase 12.

## Stock-at-Rest Rule

`warehouse_stock.quantity` and `shop_stock.quantity` are always stored in the product's base unit.

Transaction lines store both:

- the entered transaction quantity and `unit_id`
- the calculated base-unit quantity used for stock, COGS, average cost, and return validation

This keeps operational history readable while keeping inventory math consistent.

## Worked Example: PCS Base Unit With BOX Conversion

Product setup:

- Product: Bottled Water
- Base unit: `PCS`
- Conversion: `BOX = 12 PCS`
- Purchase cost: SAR 120.00 per BOX
- Selling price: SAR 15.00 per PCS

Purchase order:

- Ordered: 2 BOX
- Base ordered quantity: 2 x 12 = 24 PCS
- Unit cost entered: SAR 120.00 per BOX
- Per-base-unit cost: 120 / 12 = SAR 10.00 per PCS

Receipt:

- Received: 2 BOX
- Warehouse stock increases by 24 PCS
- Supplier bill line keeps `quantity = 2 BOX`, `base_quantity = 24 PCS`, and `unit_cost = SAR 120.00`
- Net bill amount: 2 x 120 = SAR 240.00

Average cost:

- Existing on-hand before receipt: 0 PCS
- Existing inventory value: SAR 0.00
- Received value: 24 PCS x SAR 10.00 = SAR 240.00
- New average cost: 240 / 24 = SAR 10.00 per PCS

POS sale:

- Shop stock starts at 24 PCS
- Sold: 5 PCS at SAR 15.00 per PCS
- Shop stock decreases by 5 PCS, ending at 19 PCS
- COGS uses average cost per base unit: 5 x 10 = SAR 50.00

Result:

- Warehouse/shop stock remain base-unit quantities
- Average cost remains per PCS
- Sale revenue follows the sold unit, while COGS follows base units

## Price Resolution Order

At POS sync time the server resolves the effective base-unit price in this order:

1. Customer default price category override, if the sale has a customer and the product has an override for that category
2. Shop default price category override, if no customer override applies
3. Product `price` field fallback

The server recalculates the price authoritatively during sync; client-submitted `unit_price` is not trusted.

Worked example:

- Product base price: SAR 100.00
- Wholesale override: SAR 80.00
- Customer has no default price category
- Shop default price category: Wholesale
- Sale is a walk-in/no-customer sale

Resolution:

- No customer category applies
- Shop has Wholesale as default
- Product has a Wholesale override
- Effective POS price is SAR 80.00

If that same product is sold to a customer with a VIP category and a VIP override of SAR 70.00, the customer category wins and the price is SAR 70.00. If neither customer nor shop category produces an override, the product base price of SAR 100.00 is used.

For alternate units, category prices are treated as base-unit prices and scaled by the selected unit conversion. With `BOX = 12 PCS` and a base price of SAR 10.00, selling 1 BOX uses SAR 120.00 for that transaction unit.

## Credit and Debit Note Unit Validation

Returns validate in base-unit terms.

Reasoning:

- A return can be operationally entered in a different unit from the original transaction.
- Restricting returns to the original unit would force awkward workflows, such as returning 6 PCS against an original 1 BOX sale.
- Comparing only displayed transaction quantities would be unsafe because `1 BOX` and `1 PCS` are not equivalent.

Credit notes therefore convert the requested return quantity into the product base unit and compare it with the original sale item's remaining `base_quantity`. Sellable returns add the base quantity back to `shop_stock`. The return value is multiline-safe because the original sale economics are converted back to a base-unit selling price and the original frozen `unit_cost` remains per base unit.

Debit notes use the same rule against supplier bill item `base_quantity`. Warehouse stock is reduced in base units, and the return value is calculated from the original received value per base unit. As in Phase 10, purchase returns do not recompute historical moving average cost.

## Compatibility Confirmation

Existing rows get `PCS` and matching base quantities automatically during migration:

- existing products receive `base_unit_id = PCS`
- existing purchase order items get `base_quantity_ordered = quantity_ordered` and `base_quantity_received = quantity_received`
- existing supplier bill, stock transfer, sale, credit note, and debit note lines get `base_quantity = quantity`

The default case is intentionally invisible:

- Product with no unit conversions uses `PCS`
- Product with no category price override uses `products.price`
- POS stock decrements are numerically identical because `quantity = base_quantity`
- COGS is numerically identical because `average_cost` remains per base unit
- Existing Phase 6-11 behavior is unchanged for the default PCS/no-override path

Verified regression intent:

- `test_default_unit_and_no_price_override_sale_matches_pre_phase12_behavior`
- Existing Phase 10 return tests still pass without changing their assertion intent
- Existing POS, purchasing/AP, receivables, financial statements, returns, authorization, and Phase 11 tests are included in the final regression suite