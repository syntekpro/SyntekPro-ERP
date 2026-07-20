<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('locale', 8)->nullable()->after('navigation_state');
        });

        Schema::table('business_settings', function (Blueprint $table): void {
            $table->string('default_locale', 8)->default('en')->after('date_format');
            $table->string('legal_name_ar')->nullable()->after('legal_name');
            $table->text('address_ar')->nullable()->after('address');
            $table->text('invoice_footer_text_ar')->nullable()->after('invoice_footer_text');
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table): void {
            $table->dropColumn(['default_locale', 'legal_name_ar', 'address_ar', 'invoice_footer_text_ar']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('locale');
        });
    }
};
