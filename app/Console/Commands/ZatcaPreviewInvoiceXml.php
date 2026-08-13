<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Services\Zatca\UblInvoiceBuilder;
use App\Services\Zatca\ZatcaHashChainService;
use Illuminate\Console\Command;

/**
 * Read-only preview: does not write anything back to the sale. Safe to run
 * against production data to sanity-check XML structure before wiring
 * generation into the live POS sync path.
 *
 * Usage: php artisan zatca:preview-invoice {sale_id} [--save=path.xml]
 */
class ZatcaPreviewInvoiceXml extends Command
{
    protected $signature = 'zatca:preview-invoice {sale_id} {--save=}';

    protected $description = 'Preview the UBL 2.1 XML that would be generated for a given sale (read-only, does not persist anything)';

    public function handle(ZatcaHashChainService $hashChain, UblInvoiceBuilder $builder): int
    {
        $sale = Sale::query()->with(['items', 'customer', 'shop'])->find($this->argument('sale_id'));

        if ($sale === null) {
            $this->error("Sale #{$this->argument('sale_id')} not found.");

            return self::FAILURE;
        }

        $previousHash = $hashChain->previousHashFor($sale->shop_id);
        $xml = $builder->build($sale, $previousHash);

        if ($savePath = $this->option('save')) {
            file_put_contents($savePath, $xml);
            $this->info("Saved to {$savePath}");

            return self::SUCCESS;
        }

        $this->line($xml);

        return self::SUCCESS;
    }
}
