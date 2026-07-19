<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('units.index');
    }

    public function create(): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('units.create');
    }

    public function edit(\App\Models\Unit $unit): View
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        abort_unless($user?->hasPermission('settings.manage'), 403);

        return view('units.edit', compact('unit'));
    }
}