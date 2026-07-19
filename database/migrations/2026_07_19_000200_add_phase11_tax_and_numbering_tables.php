<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_number_formats', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique();
            $table->string('label');
            $table->string('prefix', 20);
            $table->string('reset_frequency', 20)->default('never');
            $table->date('next_reset')->nullable();
            $table->timestamps();
        });

        foreach ([
            ['key' => 'sales', 'label' => 'Sale Invoice', 'prefix' => 'INV-'],
            ['key' => 'credit_note', 'label' => 'Credit Note', 'prefix' => 'CN-'],
            ['key' => 'debit_note', 'label' => 'Debit Note', 'prefix' => 'DN-'],
            ['key' => 'purchase_orders', 'label' => 'Purchase Order', 'prefix' => 'PO-'],
            ['key' => 'supplier_bills', 'label' => 'Supplier Bill', 'prefix' => 'BILL-'],
            ['key' => 'stock_transfers', 'label' => 'Stock Transfer', 'prefix' => 'ST-'],
        ] as $format) {
            DB::table('document_number_formats')->insert($format + [
                'reset_frequency' => 'never',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('products', function (Blueprint $table): void {
            $table->boolean('is_excise_applicable')->default(false)->after('vat_rate');
            $table->decimal('excise_rate', 5, 2)->nullable()->after('is_excise_applicable');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->decimal('excise_total', 12, 2)->default(0)->after('vat_total');
        });

        Schema::table('sale_items', function (Blueprint $table): void {
            $table->decimal('excise_rate', 5, 2)->nullable()->after('vat_amount');
            $table->decimal('excise_amount', 12, 2)->default(0)->after('excise_rate');
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table): void {
            $table->dropColumn(['excise_rate', 'excise_amount']);
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropColumn('excise_total');
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['is_excise_applicable', 'excise_rate']);
        });

        Schema::dropIfExists('document_number_formats');
    }
};