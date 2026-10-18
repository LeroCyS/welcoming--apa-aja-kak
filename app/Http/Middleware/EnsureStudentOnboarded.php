<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Murid baru (belum menyelesaikan setup akun) atau murid yang dilepas admin dari sekolahnya
 * diarahkan ke onboarding: tutorial → setup akun → jenjang → sekolah → Guru BK.
 */
class EnsureStudentOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        // Layar kunci PIN/Keluar Cepat tetap bisa dibuka, kalau tidak murid yang terkunci sebelum
        // menyelesaikan onboarding akan terlempar bolak-balik antara layar kunci dan onboarding.
        if ($request->user()?->needsOnboarding() && ! $request->routeIs('student.onboarding*', 'student.pin.*')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Selesaikan setup akunmu terlebih dahulu.', 'redirect' => route('student.onboarding')], 403)
                : redirect()->route('student.onboarding');
        }

        return $next($request);
    }
}
