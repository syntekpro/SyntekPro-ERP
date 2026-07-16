<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use App\Support\ShopContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveShopContext
{
    public function handle(Request $request, Closure $next): Response
    {
        ShopContext::clear();

        $firstSegment = $request->segment(1);

        if ($firstSegment !== null && ! in_array($firstSegment, ['api', 'login', 'logout', 'up'], true)) {
            $shop = Shop::query()->where('slug', $firstSegment)->first();

            if ($shop !== null) {
                ShopContext::setShop($shop);
            }
        }

        return $next($request);
    }
}
