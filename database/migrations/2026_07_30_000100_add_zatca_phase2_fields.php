<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table): void {
            // Real PIH (Previous Invoice Hash) chain, distinct from the existing
            // self-hash in `invoice_hash`. ZATCA requires each invoice's hash to
            // reference the immediately preceding invoice's hash, forming a
            // tamper-evident chain per EGS (e-invoice generation solution) unit.
            $table->string('previous_invoice_hash', 64)->nullable()->after('invoice_hash');

            // '388' invoice with subtype name: 0100000 = Standard (B2B, needs clearance),
            // 0200000 = Simplified (B2C, reported within 24h). Everything today is
            // effectively simplified; this column makes the distinction explicit.
            $table->string('zatca_invoice_type_code', 7)->default('0200000')->after('previous_invoice_hash');

            $table->longText('zatca_invoice_xml')->nullable()->after('zatca_invoice_type_code');

            $table->string('zatca_clearance_status', 20)->default('not_submitted')->after('zatca_invoice_xml');
            // not_submitted | pending | reported | cleared | failed

            $table->text('zatca_clearance_response')->nullable()->after('zatca_clearance_status');
            $table->timestamp('zatca_reported_at')->nullable()->after('zatca_clearance_response');
        });

        // Structured address is required by the UBL schema for AccountingSupplierParty.
        // The existing free-text `address` column is kept as-is and used as a fallback
        // for display purposes; these new fields back the actual XML output.
        Schema::table('business_settings', function (Blueprint $table): void {
            $table->string('street_name')->nullable()->after('address');
            $table->string('building_number', 10)->nullable()->after('street_name');
            $table->string('additional_number', 10)->nullable()->after('building_number');
            $table->string('district')->nullable()->after('additional_number');
            $table->string('city')->nullable()->after('district');
            $table->string('postal_code', 10)->nullable()->after('city');
            $table->string('country_code', 2)->default('SA')->after('postal_code');
        });

        Schema::create('zatca_certificates', function (Blueprint $table): void {
            $table->id();
            $table->string('environment', 20); // sandbox | simulation | production
            $table->string('csid_type', 20); // compliance | production
            $table->text('binary_security_token')->nullable();
            $table->text('secret')->nullable();
            $table->string('request_id')->nullable();
            // Private key is stored encrypted at the application layer (see
            // ZatcaCsrGenerator) - never write it here in plaintext.
            $table->text('private_key_encrypted')->nullable();
            $table->text('csr')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('zatca_certificates');

        Schema::table('business_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'street_name',
                'building_number',
                'additional_number',
                'district',
                'city',
                'postal_code',
                'country_code',
            ]);
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropColumn([
                'previous_invoice_hash',
                'zatca_invoice_type_code',
                'zatca_invoice_xml',
                'zatca_clearance_status',
                'zatca_clearance_response',
                'zatca_reported_at',
            ]);
        });
    }
};
