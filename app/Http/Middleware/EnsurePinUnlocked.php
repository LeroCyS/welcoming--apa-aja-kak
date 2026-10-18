<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Melindungi cerita, mood, dan jadwal murid dengan PIN 6 digit (bila aktif).
 * PIN diminta lagi setelah idle (pin_idle_minutes), setiap tab/aplikasi dibuka ulang (dicek di <head>
 * lewat sessionStorage, lihat partials/head), dan setelah Keluar Cepat.
 */
class EnsurePinUnlocked
{
    public const SESSION_KEY = 'pin_unlocked_at';

    public function handle(Request $request, Closure $next): Response
    {
        $profile = $request->user()?->studentProfile;
        if (! $profile?->pin_enabled) {
            return $next($request);
        }

        $unlockedAt = $request->session()->get(self::SESSION_KEY);
        $idle = config('welcoming.pin_idle_minutes', 15) * 60;
        if (! $unlockedAt || (now()->getTimestamp() - (int) $unlockedAt) > $idle) {
            $request->session()->forget(self::SESSION_KEY);
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Masukkan PIN untuk melanjutkan.', 'redirect' => route('student.pin.lock')], 423);
            }
            if ($request->isMethod('GET')) {
                $request->session()->put('url.intended', $request->fullUrl());
            }

            return redirect()->route('student.pin.lock');
        }

        // Hanya aktivitas nyata yang memperpanjang status terbuka. Polling otomatis (GET JSON tiap beberapa detik)
        // tidak dihitung, supaya chat yang dibiarkan terbuka tetap terkunci setelah idle.
        if (! ($request->isMethod('GET') && $request->expectsJson())) {
            $request->session()->put(self::SESSION_KEY, now()->getTimestamp());
        }

        return $next($request);
    }
}
