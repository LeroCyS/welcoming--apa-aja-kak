<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Setelah Keluar Cepat, akun murid tetap masuk tetapi terkunci: semua halaman murid (termasuk Profil
 * dan notifikasi) tertutup sampai murid memasukkan PIN atau password-nya lagi.
 */
class EnsureNotQuickLocked
{
    public const SESSION_KEY = 'quick_locked';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get(self::SESSION_KEY) || $request->routeIs('student.pin.*', 'student.conversations.emergency')) {
            return $next($request);
        }

        return $request->expectsJson()
            ? response()->json(['message' => 'Halaman terkunci.', 'redirect' => route('student.pin.lock')], 423)
            : redirect()->route('student.pin.lock');
    }
}
