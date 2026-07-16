<?php

namespace Tests\Feature;

use App\Http\Middleware\ResolveShopContext;
use App\Models\Concerns\BelongsToShop;
use App\Models\Shop;
use App\Models\User;
use App\Support\ShopContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ShopTenancyTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        ShopContext::clear();

        parent::tearDown();
    }

    public function test_resolve_shop_context_uses_the_first_path_segment_slug(): void
    {
        $shop = Shop::query()->create([
            'name' => 'Riyadh Store',
            'slug' => 'riyadh-store',
            'is_active' => true,
        ]);

        $response = (new ResolveShopContext())->handle(
            Request::create('/riyadh-store/dashboard', 'GET'),
            function () use ($shop) {
                $this->assertTrue(ShopContext::hasShop());
                $this->assertSame($shop->id, ShopContext::shopId());

                return response('ok');
            }
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_resolve_shop_context_ignores_reserved_segments(): void
    {
        ShopContext::setShopId(999);

        $response = (new ResolveShopContext())->handle(
            Request::create('/login', 'GET'),
            function () {
                $this->assertFalse(ShopContext::hasShop());
                $this->assertNull(ShopContext::shopId());

                return response('ok');
            }
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_belongs_to_shop_scope_filters_queries_to_the_active_shop_context(): void
    {
        $shopA = Shop::query()->create([
            'name' => 'Shop A',
            'slug' => 'shop-a',
            'is_active' => true,
        ]);

        $shopB = Shop::query()->create([
            'name' => 'Shop B',
            'slug' => 'shop-b',
            'is_active' => true,
        ]);

        User::factory()->create([
            'email' => 'shop-a@example.com',
            'shop_id' => $shopA->id,
        ]);

        User::factory()->create([
            'email' => 'shop-b@example.com',
            'shop_id' => $shopB->id,
        ]);

        $scopedModel = new class extends Model
        {
            use BelongsToShop;

            protected $table = 'users';

            protected $guarded = [];
        };

        $modelClass = $scopedModel::class;

        ShopContext::setShopId($shopA->id);

        $scopedEmails = $modelClass::query()
            ->orderBy('email')
            ->pluck('email')
            ->all();

        $allEmails = $modelClass::query()
            ->forAllShops()
            ->orderBy('email')
            ->pluck('email')
            ->all();

        $this->assertSame(['shop-a@example.com'], $scopedEmails);
        $this->assertSame([
            'shop-a@example.com',
            'shop-b@example.com',
        ], $allEmails);
    }
}