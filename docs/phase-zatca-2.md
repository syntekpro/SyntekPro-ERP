# ZATCA Phase 2 (Integration Phase) - Status & Plan

## Where we started

Phase 1 (Generation Phase) was already live: `App\Support\ZatcaQrEncoder` produces
a 5-tag simplified QR (seller name, VAT number, timestamp, total, VAT amount),
and `PosSaleSyncController` stamped it on every sale along with a self-hash
(`invoice_hash`) that was NOT a real PIH chain.

## What this patch adds

| Piece | File | Status |
|---|---|---|
| Real PIH chain | `app/Services/Zatca/ZatcaHashChainService.php` | Ready - not yet wired into `PosSaleSyncController` |
| UBL 2.1 XML builder | `app/Services/Zatca/UblInvoiceBuilder.php` | Structural scaffold - needs review against ZATCA's XSD before production use |
| CSR generator | `app/Services/Zatca/ZatcaCsrGenerator.php` + `zatca:generate-csr` | Usable today, no ZATCA account needed |
| XML preview command | `zatca:preview-invoice {sale_id}` | Read-only, safe to run against production data |
| CSID storage | `zatca_certificates` table + `ZatcaCertificate` model | Ready to receive compliance/production CSID once obtained |
| Structured seller address | new `business_settings` columns | Required for `PostalAddress` in the XML - needs to be filled in via Settings before invoices are submitted |

**Deliberately not done in this patch:** wiring the XML generation and PIH
chain into the live `PosSaleSyncController` sync path. That's the currently
working production flow, and it doesn't need to change until there's a real
CSID to sign against - no reason to touch a hot path before it's needed.

## What still requires manual action on your end

ZATCA Phase 2 requires onboarding through their Fatoora Portal - this is a
regulatory identity/crypto process, not something that can be built around:

1. **Fill in structured seller address** in Business Settings (street, building
   number, district, city, postal code) - needed before the XML is valid.
2. **Run `php artisan zatca:generate-csr`** with your real VAT number and
   business name. This produces `private_key.pem` (keep secret, never commit)
   and `csr.pem`.
3. **Register on ZATCA's Fatoora Portal / Developer Portal** using your
   ERAD credentials.
4. **Request an OTP** from the portal, then submit the CSR to the Compliance
   CSID endpoint (`https://gw-fatoora.zatca.gov.sa/e-invoicing/developer-portal/compliance`
   in sandbox) using the OTP. This returns a `binarySecurityToken` + `secret` -
   store these in the `zatca_certificates` table (`csid_type = compliance`).
5. **Pass compliance checks**: ZATCA requires a minimum of ~10 test invoices
   covering standard, simplified, credit note, and debit note types submitted
   through the sandbox before issuing a Compliance CSID pass.
6. **Request the Production CSID** once compliance checks pass.
7. Only then does it make sense to wire signing + reporting/clearance calls
   into the live sync path.

## Next engineering steps (once a Compliance CSID exists)

- Add a signing service that wraps the UBL XML in the required
  `ds:Signature` / `UBLExtensions` structure using the CSID's private key.
- Embed the extended QR tags (7: certificate, 8: signature, 9: public key)
  once signing is in place.
- Add `ZatcaApiClient` calls for reporting (simplified/B2C, within 24h) and
  clearance (standard/B2B, real-time, blocks invoice issuance until cleared).
- Wire `ZatcaHashChainService` and `UblInvoiceBuilder` into
  `PosSaleSyncController`, behind a config flag (`zatca.phase2.enabled`) so
  it can be turned on once everything above is verified in sandbox.
