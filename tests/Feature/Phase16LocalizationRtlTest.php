<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\BusinessSetting;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase16LocalizationRtlTest extends TestCase
{
    use RefreshDatabase;

    public function test_locale_preference_persists_per_user_across_sessions(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'locale' => 'en',
        ]);

        $this->actingAs($admin)
            ->postJson(route('user-interface-preferences.update'), [
                'locale' => 'ar',
            ])
            ->assertOk()
            ->assertJsonPath('locale', 'ar');

        $admin->refresh();
        $this->assertSame('ar', $admin->locale);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }

    public function test_login_screen_respects_business_settings_default_locale_for_guests(): void
    {
        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update([
            'default_locale' => 'ar',
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }

    public function test_guest_locale_toggle_persists_in_session_and_changes_login_language(): void
    {
        BusinessSetting::query()->firstOrCreate(['singleton_key' => 1])->update([
            'default_locale' => 'en',
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('lang="en"', false);

        $this->from(route('login'))
            ->post(route('locale.update'), [
                'locale' => 'ar',
            ])
            ->assertRedirect(route('login'));

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }

    public function test_financial_figures_render_ltr_with_western_numerals_under_arabic_locale(): void
    {
        $shop = Shop::query()->create([
            'name' => 'Riyadh',
            'slug' => 'riyadh',
            'is_active' => true,
        ]);

        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
            'locale' => 'ar',
        ]);

        $this->actingAs($cashier)
            ->get(route('pos.sales'))
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('class="ledger-total ltr-content">0.00', false)
            ->assertDontSee('٠.٠٠');
    }

    public function test_printable_document_layout_uses_arabic_business_fields_when_locale_is_arabic(): void
    {
        app()->setLocale('ar');

        $settings = BusinessSetting::query()->firstOrCreate(['singleton_key' => 1]);
        $settings->update([
            'legal_name' => 'Trading Company',
            'legal_name_ar' => 'شركة تجارية',
            'invoice_footer_text' => 'Thank you',
            'invoice_footer_text_ar' => 'شكرا لتعاملكم',
        ]);

        $document = [
            'type' => 'فاتورة بيع',
            'document_number' => 'INV-1001',
            'date' => now(),
            'counterparty_name' => 'عميل تجريبي',
            'subtotal' => 100.00,
            'vat' => 15.00,
            'total' => 115.00,
            'lines' => [
                [
                    'description' => 'منتج اختباري',
                    'quantity' => 1,
                    'unit' => 'PCS',
                    'unit_price' => 100,
                    'vat_rate' => 15,
                    'line_total' => 115,
                ],
            ],
        ];

        $this->view('documents.print', [
            'document' => $document,
            'businessSettings' => $settings->fresh(),
            'logoUrl' => '/images/logo-full.png',
            'format' => 'standard',
        ])
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false)
            ->assertSee('شركة تجارية')
            ->assertSee('شكرا لتعاملكم')
            ->assertSee('class="ltr-content">SAR 115.00', false);
    }
}
