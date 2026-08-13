<?php

namespace App\Console\Commands;

use App\Services\Zatca\ZatcaCsrGenerator;
use Illuminate\Console\Command;

/**
 * Usage:
 *   php artisan zatca:generate-csr \
 *     --common-name="SyntekPro ERP" \
 *     --org-id=300000000000003 \
 *     --org-unit="Riyadh Branch" \
 *     --org-name="Your Registered Business Name" \
 *     --out=storage/app/zatca
 *
 * Outputs private_key.pem (KEEP SECRET, do not commit) and csr.pem (this is
 * what gets submitted to ZATCA's compliance CSID endpoint / developer
 * portal to begin onboarding).
 */
class ZatcaGenerateCsr extends Command
{
    protected $signature = 'zatca:generate-csr
        {--common-name= : Solution/unit common name}
        {--org-id= : 15-digit VAT registration number}
        {--org-unit= : Branch/unit name}
        {--org-name= : Registered legal business name}
        {--out=storage/app/zatca : Output directory}';

    protected $description = 'Generate the EC private key and CSR needed for ZATCA Compliance CSID onboarding';

    public function handle(ZatcaCsrGenerator $generator): int
    {
        $required = ['common-name', 'org-id', 'org-unit', 'org-name'];
        foreach ($required as $option) {
            if (!$this->option($option)) {
                $this->error("Missing required option --{$option}");

                return self::FAILURE;
            }
        }

        $result = $generator->generate([
            'common_name' => $this->option('common-name'),
            'organization_identifier' => $this->option('org-id'),
            'organization_unit_name' => $this->option('org-unit'),
            'organization_name' => $this->option('org-name'),
        ]);

        $outDir = rtrim($this->option('out'), '/');
        if (!is_dir($outDir)) {
            mkdir($outDir, 0700, true);
        }

        file_put_contents("{$outDir}/private_key.pem", $result['private_key']);
        chmod("{$outDir}/private_key.pem", 0600);
        file_put_contents("{$outDir}/csr.pem", $result['csr']);

        $this->info("Private key written to {$outDir}/private_key.pem (keep this secret - do not commit or share it)");
        $this->info("CSR written to {$outDir}/csr.pem (submit this to ZATCA's Compliance CSID endpoint)");
        $this->warn('Note: ZATCA also expects organizationIdentifier and invoice-type/location/industry fields inside the CSR via a custom OpenSSL [req_ext] config block. This command covers the standard DN fields only - see docs/phase-zatca-2.md before submitting to ZATCA.');

        return self::SUCCESS;
    }
}
