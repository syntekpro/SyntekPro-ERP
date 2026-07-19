<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('name');
            $table->string('image_path')->nullable()->after('barcode');
            $table->decimal('stock_min', 12, 3)->nullable()->after('excise_rate');
            $table->decimal('stock_max', 12, 3)->nullable()->after('stock_min');
        });

        Schema::create('document_shares', function (Blueprint $table): void {
            $table->id();
            $table->string('document_type', 80);
            $table->unsignedBigInteger('document_id');
            $table->string('token', 96)->unique();
            $table->timestamp('expires_at')->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_shares');

        Schema::table('products', function (Blueprint $table): void {
            $table->dropColumn(['description', 'image_path', 'stock_min', 'stock_max']);
        });
    }
};
