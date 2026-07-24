<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('product-categories.index');
    }

    public function create(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('product-categories.create');
    }

    public function edit(\App\Models\ProductCategory $productCategory): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('product-categories.edit', compact('productCategory'));
    }
}
