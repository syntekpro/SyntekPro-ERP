<?php

namespace App\Services\Zatca;

use App\Models\Sale;

/**
 * ZATCA requires every e-invoice to embed the hash of the invoice that came
 * immediately before it, forming a per-EGS-unit chain that makes silent
 * deletion or reordering of invoices detectable. The very first invoice in a
 * chain uses a fixed genesis value (base64 of a single zero byte).
 *
 * This implementation chains per shop, since each shop currently maps to one
 * POS/EGS unit in the data model. If a single shop is ever served by more
 * than one physical device concurrently, the chain must be scoped per
 * device instead - revisit before relying on this for multi-till shops.
 */
class ZatcaHashChainService
{
    public const GENESIS_HASH = 'NWZlY2ViNjZmZmM4NmYzOGQ5NTI3ODZjNmQ2OTZjNzljMmRiYzIzOWRkNGU5MWI0NjcyOWQ3M2EyN2ZiNTdlOQ==';

    public function previousHashFor(int $shopId): string
    {
        $lastSale = Sale::query()
            ->where('shop_id', $shopId)
            ->whereNotNull('invoice_hash')
            ->orderByDesc('id')
            ->first();

        return $lastSale?->invoice_hash ?: self::GENESIS_HASH;
    }

    /**
     * Compute the invoice's own hash. Per ZATCA guidance this must be the
     * SHA-256 hash of the canonicalized invoice XML, not a hash of arbitrary
     * fields. Until the invoice is actually cryptographically stamped (needs
     * a production CSID), this is a stand-in over the fields that make up
     * the invoice - replace with hash-of-XML once UblInvoiceBuilder output
     * is finalized and signing is wired up.
     */
    public function computeInvoiceHash(Sale $sale, string $previousHash): string
    {
        return hash('sha256', implode('|', [
            $previousHash,
            $sale->invoice_uuid,
            $sale->total,
            $sale->vat_total,
            $sale->sold_at,
        ]));
    }
}
