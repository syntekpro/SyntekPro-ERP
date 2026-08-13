<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table): void {
            $table->id();
            // The GL account (must be account_type = asset) that this bank
            // account's balance posts against. Reconciliation compares this
            // account's journal_entry_lines against imported statement lines.
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('bank_name');
            $table->string('account_holder_name')->nullable();
            // Store masked, e.g. "****4821" - never the full account number.
            $table->string('account_number_last4', 10)->nullable();
            $table->string('iban', 34)->nullable();
            $table->string('currency_code', 3)->default('SAR');
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->date('opening_balance_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bank_statement_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->string('original_filename');
            // JSON map of {csv_column_index: field_name} chosen at import time,
            // e.g. {"0": "transaction_date", "1": "description", "3": "amount"}.
            // Kept per-import (not per-bank-account) since a bank can change
            // its export format between statements.
            $table->json('column_mapping');
            $table->unsignedInteger('row_count')->default(0);
            $table->unsignedInteger('imported_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bank_statement_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bank_statement_import_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date')->index();
            $table->string('description');
            $table->string('reference')->nullable();
            // Signed amount: positive = money in, negative = money out.
            // Keeping a single signed column rather than separate debit/credit
            // columns since bank CSV exports are inconsistent about this and
            // it's simpler to normalize to one sign convention at import time.
            $table->decimal('amount', 14, 2);
            $table->decimal('running_balance', 14, 2)->nullable();

            $table->string('match_status', 20)->default('unmatched');
            // unmatched | matched | ignored
            $table->foreignId('matched_journal_entry_line_id')->nullable()
                ->constrained('journal_entry_lines')->nullOnDelete();
            $table->foreignId('matched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('matched_at')->nullable();

            $table->timestamps();

            $table->index(['bank_account_id', 'match_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_statement_lines');
        Schema::dropIfExists('bank_statement_imports');
        Schema::dropIfExists('bank_accounts');
    }
};
