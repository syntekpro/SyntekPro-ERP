<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(['en', 'ar'])],
        ]);

        $locale = (string) $validated['locale'];
        $request->session()->put('locale', $locale);

        $user = $request->user();
        if ($user !== null) {
            $user->locale = $locale;
            $user->save();
        }

        return back();
    }
}
