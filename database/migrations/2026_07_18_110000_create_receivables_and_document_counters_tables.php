<?php

use App\Enums\SalePaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_counters', function (Blueprint $table): void {
            $table->id();
            $table->string('key', 80)->unique();
            $table->unsignedBigInteger('next_number')->default(1);
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('code', 40)->unique();
            $table->string('contact_name')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->string('vat_registration_number', 32)->nullable();
            $table->unsignedInteger('payment_terms_days')->default(30);
            $table->decimal('credit_limit', 14, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->string('invoice_number', 40)->nullable()->unique()->after('idempotency_key');
            $table->string('payment_method', 30)->default(SalePaymentMethod::Cash->value)->index()->after('total');
            $table->foreignId('customer_id')->nullable()->after('payment_method')->constrained('customers')->restrictOnDelete();
            $table->date('due_date')->nullable()->index()->after('customer_id');
            $table->decimal('outstanding_balance', 12, 2)->default(0)->index()->after('due_date');
        });

        Schema::create('customer_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->date('paid_at');
            $table->string('reference', 120)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_payments');

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['invoice_number', 'payment_method', 'customer_id', 'due_date', 'outstanding_balance']);
        });

        Schema::dropIfExists('customers');
        Schema::dropIfExists('document_counters');
    }
};
