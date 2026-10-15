<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        if (! in_array($locale, ['id', 'en'], true)) {
            $locale = 'id';
        }

        session(['locale' => $locale]);

        if ($user = $request->user()) {
            $user->update(['locale' => $locale]);
        }

        return back()->withCookie(cookie()->forever('turget_locale', $locale));
    }
}
