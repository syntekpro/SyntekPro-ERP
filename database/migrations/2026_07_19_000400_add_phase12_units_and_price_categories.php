<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('units')->insert([
            'code' => 'PCS',
            'name' => 'Piece',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pcsUnitId = (int) DB::table('units')->where('code', 'PCS')->value('id');

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('base_unit_id')->nullable()->after('barcode')->constrained('units')->restrictOnDelete();
        });

        DB::table('products')->update(['base_unit_id' => $pcsUnitId]);

        Schema::create('product_unit_conversions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->restrictOnDelete();
            $table->decimal('conversion_factor', 14, 6);
            $table->timestamps();

            $table->unique(['product_id', 'unit_id']);
        });

        Schema::create('price_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('price_categories')->insert([
            'name' => 'Retail',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('product_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_category_id')->constrained('price_categories')->cascadeOnDelete();
            $table->decimal('price', 12, 2);
            $table->timestamps();

            $table->unique(['product_id', 'price_category_id']);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->foreignId('default_price_category_id')->nullable()->after('credit_limit')->constrained('price_categories')->nullOnDelete();
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->foreignId('default_price_category_id')->nullable()->after('vat_registration_number')->constrained('price_categories')->nullOnDelete();
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->restrictOnDelete();
            $table->decimal('base_quantity_ordered', 12, 3)->default(0)->after('quantity_ordered');
            $table->decimal('base_quantity_received', 12, 3)->default(0)->after('quantity_received');
        });

        DB::table('purchase_order_items')->update([
            'unit_id' => $pcsUnitId,
            'base_quantity_ordered' => DB::raw('quantity_ordered'),
            'base_quantity_received' => DB::raw('quantity_received'),
        ]);

        Schema::table('supplier_bill_items', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->restrictOnDelete();
            $table->decimal('base_quantity', 12, 3)->default(0)->after('quantity');
        });

        DB::table('supplier_bill_items')->update([
            'unit_id' => $pcsUnitId,
            'base_quantity' => DB::raw('quantity'),
        ]);

        Schema::table('stock_transfer_items', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->restrictOnDelete();
            $table->decimal('base_quantity', 12, 3)->default(0)->after('quantity');
        });

        DB::table('stock_transfer_items')->update([
            'unit_id' => $pcsUnitId,
            'base_quantity' => DB::raw('quantity'),
        ]);

        Schema::table('sale_items', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->restrictOnDelete();
            $table->decimal('base_quantity', 12, 3)->default(0)->after('quantity');
        });

        DB::table('sale_items')->update([
            'unit_id' => $pcsUnitId,
            'base_quantity' => DB::raw('quantity'),
        ]);

        Schema::table('credit_note_items', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->restrictOnDelete();
            $table->decimal('base_quantity', 12, 3)->default(0)->after('quantity');
        });

        DB::table('credit_note_items')->update([
            'unit_id' => $pcsUnitId,
            'base_quantity' => DB::raw('quantity'),
        ]);

        Schema::table('debit_note_items', function (Blueprint $table): void {
            $table->foreignId('unit_id')->nullable()->after('product_id')->constrained('units')->restrictOnDelete();
            $table->decimal('base_quantity', 12, 3)->default(0)->after('quantity');
        });

        DB::table('debit_note_items')->update([
            'unit_id' => $pcsUnitId,
            'base_quantity' => DB::raw('quantity'),
        ]);
    }

    public function down(): void
    {
        Schema::table('debit_note_items', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'base_quantity']);
        });

        Schema::table('credit_note_items', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'base_quantity']);
        });

        Schema::table('sale_items', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'base_quantity']);
        });

        Schema::table('stock_transfer_items', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'base_quantity']);
        });

        Schema::table('supplier_bill_items', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'base_quantity']);
        });

        Schema::table('purchase_order_items', function (Blueprint $table): void {
            $table->dropForeign(['unit_id']);
            $table->dropColumn(['unit_id', 'base_quantity_ordered', 'base_quantity_received']);
        });

        Schema::table('shops', function (Blueprint $table): void {
            $table->dropForeign(['default_price_category_id']);
            $table->dropColumn('default_price_category_id');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropForeign(['default_price_category_id']);
            $table->dropColumn('default_price_category_id');
        });

        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('price_categories');
        Schema::dropIfExists('product_unit_conversions');

        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['base_unit_id']);
            $table->dropColumn('base_unit_id');
        });

        Schema::dropIfExists('units');
    }
};