<?php

namespace App\Http\Middleware;

use App\Models\SlugRedirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSlugRedirect
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('GET')) {
            $redirect = SlugRedirect::findMatch($request->getPathInfo());
            if ($redirect) {
                return redirect($redirect->to_path, $redirect->status_code);
            }
        }

        return $next($request);
    }
}
