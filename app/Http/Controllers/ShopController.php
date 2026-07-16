<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Shop::class);

        return view('shops.index');
    }

    public function create(): View
    {
        $this->authorize('create', Shop::class);

        return view('shops.create');
    }

    public function edit(Shop $shop): View
    {
        $this->authorize('update', $shop);

        return view('shops.edit', compact('shop'));
    }
}