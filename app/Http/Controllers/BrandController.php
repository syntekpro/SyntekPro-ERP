<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BrandController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('brands.index');
    }

    public function create(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('brands.create');
    }

    public function edit(\App\Models\Brand $brand): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('brands.edit', compact('brand'));
    }
}
