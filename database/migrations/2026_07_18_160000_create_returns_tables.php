<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table): void {
            $table->id();
            $table->string('credit_note_number', 40)->unique();
            $table->foreignId('sale_id')->constrained()->restrictOnDelete();
            $table->foreignId('shop_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->date('note_date')->index();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('vat_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->decimal('applied_to_sale_balance', 14, 2)->default(0);
            $table->decimal('refund_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('credit_note_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('credit_note_id')->constrained('credit_notes')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained('sale_items')->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 12, 3);
            $table->string('condition', 20);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('vat_rate', 12, 2)->default(0);
            $table->decimal('net_amount', 14, 2);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('gross_amount', 14, 2);
            $table->timestamps();
        });

        Schema::create('debit_notes', function (Blueprint $table): void {
            $table->id();
            $table->string('debit_note_number', 40)->unique();
            $table->foreignId('supplier_bill_id')->constrained('supplier_bills')->restrictOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('supplier_id')->constrained()->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained()->restrictOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->date('note_date')->index();
            $table->decimal('subtotal', 14, 2);
            $table->decimal('vat_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->decimal('applied_to_bill_balance', 14, 2)->default(0);
            $table->decimal('excess_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('debit_note_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('debit_note_id')->constrained('debit_notes')->cascadeOnDelete();
            $table->foreignId('supplier_bill_item_id')->constrained('supplier_bill_items')->restrictOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('description');
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_cost', 14, 2);
            $table->decimal('vat_rate', 12, 2)->default(0);
            $table->decimal('net_amount', 14, 2);
            $table->decimal('vat_amount', 14, 2)->default(0);
            $table->decimal('gross_amount', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_note_items');
        Schema::dropIfExists('debit_notes');
        Schema::dropIfExists('credit_note_items');
        Schema::dropIfExists('credit_notes');
    }
};