<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'same-origin');
        $response->headers->set('Permissions-Policy', 'microphone=(self), camera=(), geolocation=()');

        // Halaman yang butuh login tidak boleh disimpan browser: setelah Keluar Cepat/Keluar Akun,
        // tombol Back atau membuka ulang tab tidak boleh menampilkan Profil, cerita, dsb.
        if ($request->user() || $request->routeIs('quick-exit', 'logout')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}
