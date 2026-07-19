<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('singleton_key')->default(1)->unique();
            $table->string('legal_name')->nullable();
            $table->string('cr_number', 64)->nullable();
            $table->string('vat_number', 32)->nullable();
            $table->text('address')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('email')->nullable();
            $table->boolean('vat_enabled')->default(true);
            $table->decimal('vat_rate', 5, 2)->default(15.00);
            $table->string('currency_code', 3)->default('SAR');
            $table->string('currency_symbol', 12)->default('SAR');
            $table->unsignedTinyInteger('quantity_decimal_places')->default(3);
            $table->unsignedTinyInteger('price_decimal_places')->default(2);
            $table->string('date_format', 40)->default('Y-m-d');
            $table->string('theme', 40)->default('syntek-default');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->text('invoice_footer_text')->nullable();
            $table->string('mail_from_name')->nullable();
            $table->string('mail_from_address')->nullable();
            $table->timestamps();
        });

        DB::table('business_settings')->insert([
            'singleton_key' => 1,
            'legal_name' => env('ZATCA_SELLER_LEGAL_NAME'),
            'vat_number' => env('ZATCA_SELLER_VAT_NUMBER'),
            'vat_enabled' => true,
            'vat_rate' => 15.00,
            'currency_code' => 'SAR',
            'currency_symbol' => 'SAR',
            'quantity_decimal_places' => 3,
            'price_decimal_places' => 2,
            'date_format' => 'Y-m-d',
            'theme' => 'syntek-default',
            'mail_from_name' => config('mail.from.name'),
            'mail_from_address' => config('mail.from.address'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('business_settings');
    }
};