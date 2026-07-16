<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_active')->default(true)->after('shop_id');
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->string('legal_name')->nullable()->after('name');
            $table->string('vat_registration_number', 32)->nullable()->after('legal_name');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('cost_price', 12, 2)->default(0)->after('price');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->text('zatca_qr_payload')->nullable()->after('payload_hash');
            $table->uuid('invoice_uuid')->nullable()->after('zatca_qr_payload');
            $table->string('invoice_hash', 64)->nullable()->after('invoice_uuid');
        });

        Schema::table('sale_items', function (Blueprint $table): void {
            $table->decimal('unit_cost', 12, 2)->default(0)->after('unit_price');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table): void {
            $table->dropColumn('unit_cost');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropColumn(['zatca_qr_payload', 'invoice_uuid', 'invoice_hash']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('cost_price');
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->dropColumn(['legal_name', 'vat_registration_number']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('is_active');
        });
    }
};
