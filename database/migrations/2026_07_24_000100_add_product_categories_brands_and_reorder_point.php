<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('brands', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->foreignId('product_category_id')->nullable()->after('base_unit_id')->constrained('product_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('product_category_id')->constrained('brands')->nullOnDelete();
            $table->decimal('stock_reorder_point', 12, 3)->nullable()->after('stock_min');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropForeign(['product_category_id']);
            $table->dropForeign(['brand_id']);
            $table->dropColumn(['product_category_id', 'brand_id', 'stock_reorder_point']);
        });

        Schema::dropIfExists('brands');
        Schema::dropIfExists('product_categories');
    }
};
