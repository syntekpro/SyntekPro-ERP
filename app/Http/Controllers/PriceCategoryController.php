<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PriceCategoryController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('price-categories.index');
    }

    public function create(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('price-categories.create');
    }

    public function edit(\App\Models\PriceCategory $priceCategory): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('price-categories.edit', compact('priceCategory'));
    }
}