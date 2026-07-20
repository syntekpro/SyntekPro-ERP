<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('business_settings', function (Blueprint $table): void {
            $table->string('application_name')->nullable()->after('singleton_key');
            $table->string('application_short_name', 32)->nullable()->after('application_name');

            $table->string('brand_primary_color', 20)->nullable()->after('theme');
            $table->string('brand_accent_color', 20)->nullable()->after('brand_primary_color');
            $table->string('brand_background_color', 20)->nullable()->after('brand_accent_color');
            $table->string('brand_surface_color', 20)->nullable()->after('brand_background_color');

            $table->string('touch_icon_path')->nullable()->after('favicon_path');

            $table->string('login_title')->nullable()->after('touch_icon_path');
            $table->string('login_subtitle')->nullable()->after('login_title');

            $table->string('header_brand_text')->nullable()->after('login_subtitle');
            $table->string('header_brand_subtext')->nullable()->after('header_brand_text');

            $table->boolean('footer_show_powered_by')->default(true)->after('header_brand_subtext');
            $table->string('footer_powered_by_text')->nullable()->after('footer_show_powered_by');
            $table->string('brand_website')->nullable()->after('footer_powered_by_text');

            $table->string('email_branding_header')->nullable()->after('brand_website');
            $table->text('email_branding_footer')->nullable()->after('email_branding_header');

            $table->string('pdf_branding_header')->nullable()->after('email_branding_footer');
            $table->text('pdf_branding_footer')->nullable()->after('pdf_branding_header');
            $table->string('pdf_watermark_text')->nullable()->after('pdf_branding_footer');
        });
    }

    public function down(): void
    {
        Schema::table('business_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'application_name',
                'application_short_name',
                'brand_primary_color',
                'brand_accent_color',
                'brand_background_color',
                'brand_surface_color',
                'touch_icon_path',
                'login_title',
                'login_subtitle',
                'header_brand_text',
                'header_brand_subtext',
                'footer_show_powered_by',
                'footer_powered_by_text',
                'brand_website',
                'email_branding_header',
                'email_branding_footer',
                'pdf_branding_header',
                'pdf_branding_footer',
                'pdf_watermark_text',
            ]);
        });
    }
};
