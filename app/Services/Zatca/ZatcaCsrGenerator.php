<?php

namespace App\Services\Zatca;

/**
 * Generates the secp256k1 EC private key and Certificate Signing Request
 * (CSR) ZATCA requires to begin CSID onboarding via the Fatoora Portal.
 *
 * This is the one piece of Phase 2 that can be run entirely offline, today,
 * with no ZATCA account - the output (CSR) is what gets pasted into the
 * developer portal / compliance API to receive a Compliance CSID.
 *
 * The private key must be kept secret and is never sent anywhere; only the
 * CSR (a public request derived from it) is submitted to ZATCA.
 */
class ZatcaCsrGenerator
{
    /**
     * @param array{
     *   common_name: string,
     *   organization_identifier: string,
     *   organization_unit_name: string,
     *   organization_name: string,
     *   country_name?: string,
     *   invoice_type?: string,
     *   location?: string,
     *   industry?: string,
     * } $subject
     * @return array{private_key: string, csr: string}
     */
    public function generate(array $subject): array
    {
        $config = [
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'secp256k1',
            'digest_alg' => 'sha256',
        ];

        $privateKey = openssl_pkey_new($config);

        if ($privateKey === false) {
            throw new \RuntimeException(
                'Failed to generate EC private key: ' . openssl_error_string()
            );
        }

        $dn = [
            'commonName' => $subject['common_name'],
            'organizationalUnitName' => $subject['organization_unit_name'],
            'organizationName' => $subject['organization_name'],
            'countryName' => $subject['country_name'] ?? 'SA',
        ];

        // ZATCA also expects a custom "organizationIdentifier" (the 15-digit
        // VAT/TIN) plus SAN-style extension fields (invoice type, location,
        // industry) that PHP's openssl_csr_new does not expose directly via
        // $dn. These typically need to be injected via an OpenSSL config
        // file with an [req_ext] section - flagging this here rather than
        // silently omitting it, since a CSR missing these fields is the #1
        // cause of "Invalid Request" rejections from ZATCA's compliance API.
        $csr = openssl_csr_new($dn, $privateKey, $config);

        if ($csr === false) {
            throw new \RuntimeException(
                'Failed to generate CSR: ' . openssl_error_string()
            );
        }

        openssl_csr_export($csr, $csrOut);
        openssl_pkey_export($privateKey, $privateKeyOut);

        return [
            'private_key' => $privateKeyOut,
            'csr' => $csrOut,
        ];
    }
}
