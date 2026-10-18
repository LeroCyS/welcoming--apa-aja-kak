<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password && ! $request->routeIs('password.change*', 'logout', 'quick-exit')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Ganti password terlebih dahulu.'], 403)
                : redirect()->route('password.change');
        }

        return $next($request);
    }
}
