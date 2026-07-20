<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserInterfacePreferenceController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'theme_mode' => ['sometimes', 'nullable', Rule::in(['light', 'dark', 'system'])],
            'locale' => ['sometimes', Rule::in(['en', 'ar'])],
            'navigation_state' => ['sometimes', 'array'],
            'navigation_state.collapsed_sections' => ['sometimes', 'array'],
            'navigation_state.collapsed_sections.*' => ['string', 'max:80'],
        ]);

        $user = $request->user();

        if (array_key_exists('theme_mode', $validated)) {
            $user->theme_mode = $validated['theme_mode'];
        }

        if (array_key_exists('locale', $validated)) {
            $user->locale = $validated['locale'];
            $request->session()->put('locale', $validated['locale']);
        }

        if (array_key_exists('navigation_state', $validated)) {
            $user->navigation_state = array_replace_recursive($user->navigation_state ?? [], $validated['navigation_state']);
        }

        $user->save();

        return response()->json([
            'theme_mode' => $user->theme_mode,
            'locale' => $user->locale,
            'navigation_state' => $user->navigation_state ?? [],
        ]);
    }
}