<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __invoke(Request $request): View
    {
        abort_unless($request->user()?->hasPermission('settings.manage'), 403);

        return view('settings.index');
    }
}