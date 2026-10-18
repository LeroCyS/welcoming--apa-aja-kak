<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureNotQuickLocked;
use App\Http\Middleware\EnsurePinUnlocked;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Dipanggil via navigator.sendBeacon saat Keluar Cepat, sementara browser langsung pindah ke situs netral.
 * Berbeda dengan Keluar Akun: murid tidak di-logout, tetapi akunnya dikunci sampai PIN/password dimasukkan lagi,
 * jadi orang yang mengintip tidak bisa membuka apa pun walau menekan Back.
 * Akun Google tanpa PIN maupun password tidak punya cara membuka kunci, jadi di-logout penuh.
 */
class QuickExitController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $canUnlock = $user?->isStudent() && ($user->studentProfile?->pin_enabled || $user->password !== null);

        if ($canUnlock) {
            $request->session()->forget(EnsurePinUnlocked::SESSION_KEY);
            $request->session()->put(EnsureNotQuickLocked::SESSION_KEY, true);
            $request->session()->regenerateToken();

            return response()->noContent();
        }

        if ($user) {
            Auth::logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
