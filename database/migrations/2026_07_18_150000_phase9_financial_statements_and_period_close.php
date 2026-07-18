<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->decimal('average_cost', 14, 4)->default(0)->after('cost_price');
        });

        DB::table('products')->update([
            'average_cost' => DB::raw('cost_price'),
        ]);

        Schema::table('journal_entries', function (Blueprint $table): void {
            $table->dropForeign(['shop_id']);
            $table->foreignId('shop_id')->nullable()->change();
            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();
        });

        DB::table('journal_entries')
            ->whereIn('source', ['supplier_bill', 'supplier_payment', 'customer_payment'])
            ->update(['shop_id' => null]);

        Schema::create('fiscal_periods', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('period_start');
            $table->date('period_end');
            $table->boolean('is_closed')->default(false);
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month']);
            $table->index(['period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_periods');

        Schema::table('journal_entries', function (Blueprint $table): void {
            $table->dropForeign(['shop_id']);
            $table->foreignId('shop_id')->nullable(false)->change();
            $table->foreign('shop_id')->references('id')->on('shops')->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn('average_cost');
        });
    }
};
