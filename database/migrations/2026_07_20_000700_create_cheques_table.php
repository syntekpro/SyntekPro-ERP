<?php

use App\Enums\ChequeStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table): void {
            $table->id();
            $table->string('direction', 20)->index();
            $table->string('cheque_number', 80);
            $table->string('bank_name', 120);
            $table->date('cheque_date')->index();
            $table->decimal('amount', 14, 2);
            $table->string('status', 20)->default(ChequeStatus::Pending->value)->index();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->cascadeOnDelete();
            $table->foreignId('supplier_bill_id')->nullable()->constrained('supplier_bills')->cascadeOnDelete();
            $table->foreignId('recorded_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('cleared_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->foreignId('bounced_journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            $table->date('cleared_at')->nullable();
            $table->date('bounced_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['direction', 'status']);
            $table->index(['sale_id', 'status']);
            $table->index(['supplier_bill_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheques');
    }
};
