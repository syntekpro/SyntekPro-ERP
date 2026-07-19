<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase13NavigationDesignSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_interface_preferences_persist_per_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->postJson(route('user-interface-preferences.update'), [
                'theme_mode' => 'dark',
                'navigation_state' => ['collapsed_sections' => ['operations', 'reports']],
            ])
            ->assertOk()
            ->assertJsonPath('theme_mode', 'dark')
            ->assertJsonPath('navigation_state.collapsed_sections.0', 'operations')
            ->assertJsonPath('navigation_state.collapsed_sections.1', 'reports');

        $admin->refresh();

        $this->assertSame('dark', $admin->theme_mode);
        $this->assertSame(['operations', 'reports'], $admin->navigation_state['collapsed_sections']);
    }

    public function test_hub_layout_renders_grouped_navigation_command_palette_and_theme_state(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
            'theme_mode' => 'dark',
            'navigation_state' => ['collapsed_sections' => ['operations']],
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-theme="dark"', false)
            ->assertSee('data-nav-section="operations"', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('Operations')
            ->assertSee('Purchasing')
            ->assertSee('Sales')
            ->assertSee('Accounting')
            ->assertSee('Reports')
            ->assertSee('Administration')
            ->assertSee('data-command-palette', false)
            ->assertSee('navigation-commands', false)
            ->assertSee('Customer Receivables')
            ->assertSee('Settings / Roles / Branding');
    }

    public function test_login_and_pos_load_dynamic_theme_after_compiled_assets(): void
    {
        $shop = Shop::query()->create(['name' => 'Riyadh', 'slug' => 'riyadh', 'is_active' => true]);
        $cashier = User::factory()->create([
            'role' => UserRole::Cashier,
            'shop_id' => $shop->id,
            'theme_mode' => 'light',
        ]);

        $login = $this->get(route('login'))->assertOk()->getContent();
        $this->assertLessThan(strpos($login, 'theme.css'), strpos($login, 'resources/css/app.css'));

        $pos = $this->actingAs($cashier)->get(route('pos.sales'))->assertOk()->getContent();
        $this->assertStringContainsString('data-theme="light"', $pos);
        $this->assertLessThan(strpos($pos, 'theme.css'), strpos($pos, 'resources/css/app.css'));
    }
}
