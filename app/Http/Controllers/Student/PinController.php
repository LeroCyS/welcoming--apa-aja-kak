<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureNotQuickLocked;
use App\Http\Middleware\EnsurePinUnlocked;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PinController extends Controller
{
    public function enable(Request $request): RedirectResponse
    {
        $request->validateWithBag('pin', [
            'pin' => ['required', 'digits:6', 'confirmed'],
        ], ['pin.digits' => 'PIN harus 6 angka.', 'pin.confirmed' => 'PIN yang diulang belum sama.']);

        $request->user()->studentProfile->update([
            'pin_hash' => Hash::make($request->input('pin')),
            'pin_enabled' => true,
            'pin_failed_attempts' => 0,
            'pin_locked_until' => null,
        ]);
        $request->session()->put(EnsurePinUnlocked::SESSION_KEY, now()->getTimestamp());

        return redirect()->route('student.profile')->with('status', 'Kunci PIN aktif. Cerita, mood, dan jadwalmu sekarang terlindungi.');
    }

    public function disable(Request $request): RedirectResponse
    {
        $request->validateWithBag('pin', ['current_pin' => ['required', 'digits:6']], ['current_pin.*' => 'Masukkan PIN 6 angka saat ini.']);
        $profile = $request->user()->studentProfile;
        if (! Hash::check($request->input('current_pin'), (string) $profile->pin_hash)) {
            return back()->withErrors(['current_pin' => 'PIN belum benar.'], 'pin');
        }
        $profile->update(['pin_hash' => null, 'pin_enabled' => false, 'pin_failed_attempts' => 0, 'pin_locked_until' => null]);

        return redirect()->route('student.profile')->with('status', 'Kunci PIN dimatikan.');
    }

    /**
     * Layar kunci: dipakai untuk PIN (idle, tab dibuka ulang) dan setelah Keluar Cepat.
     * Bila PIN tidak aktif, kunci Keluar Cepat dibuka dengan password akun.
     */
    public function lock(Request $request): View|RedirectResponse
    {
        $profile = $request->user()->studentProfile;
        $quickLocked = (bool) $request->session()->get(EnsureNotQuickLocked::SESSION_KEY);
        if (! $profile->pin_enabled && ! $quickLocked) {
            return redirect()->route('student.home');
        }
        // Tab/aplikasi dibuka ulang: PIN wajib dimasukkan lagi walau sesi masih terbuka,
        // lalu murid dikembalikan ke halaman yang tadi ia buka (mis. menu Cerita), bukan ke Beranda.
        if ($request->boolean('ulang')) {
            $request->session()->forget(EnsurePinUnlocked::SESSION_KEY);
            $back = (string) $request->query('kembali');
            if (str_starts_with($back, '/murid/') && ! str_starts_with($back, '//')) {
                $request->session()->put('url.intended', url($back));
            }
        }

        return view('student.pin-lock', [
            'mode' => $profile->pin_enabled ? 'pin' : 'password',
            'quickLocked' => $quickLocked,
            'lockedUntil' => $profile->pin_locked_until?->isFuture() ? $profile->pin_locked_until : null,
        ]);
    }

    public function unlock(Request $request): RedirectResponse
    {
        $profile = $request->user()->studentProfile;
        if (! $profile->pin_enabled) {
            return $this->unlockWithPassword($request);
        }
        $request->validate(['pin' => ['required', 'digits:6']], ['pin.*' => 'Masukkan 6 angka PIN-mu.']);

        if ($profile->pin_locked_until?->isFuture()) {
            return back()->withErrors(['pin' => 'Terlalu banyak percobaan. Coba lagi setelah '.$profile->pin_locked_until->diffForHumans(null, true).'.']);
        }

        if (! Hash::check($request->input('pin'), (string) $profile->pin_hash)) {
            $attempts = $profile->pin_failed_attempts + 1;
            $locked = $attempts >= 5;
            $profile->update([
                'pin_failed_attempts' => $locked ? 0 : $attempts,
                'pin_locked_until' => $locked ? now()->addMinutes(5) : null,
            ]);

            return back()->withErrors(['pin' => $locked ? 'PIN salah 5 kali. Terkunci 5 menit demi keamananmu.' : 'PIN belum benar. Sisa '.(5 - $attempts).' percobaan.']);
        }

        $profile->update(['pin_failed_attempts' => 0, 'pin_locked_until' => null]);

        return $this->unlocked($request);
    }

    protected function unlockWithPassword(Request $request): RedirectResponse
    {
        abort_unless($request->session()->get(EnsureNotQuickLocked::SESSION_KEY), 404);
        $request->validate(['password' => ['required', 'current_password']], [
            'password.required' => 'Masukkan password akunmu.',
            'password.current_password' => 'Password belum benar.',
        ]);

        return $this->unlocked($request);
    }

    protected function unlocked(Request $request): RedirectResponse
    {
        $request->session()->forget(EnsureNotQuickLocked::SESSION_KEY);
        $request->session()->put(EnsurePinUnlocked::SESSION_KEY, now()->getTimestamp());

        return redirect()->intended(route('student.home'));
    }

    /** Lupa PIN: verifikasi password akun → PIN dinonaktifkan. */
    public function forgot(Request $request): RedirectResponse
    {
        $request->validateWithBag('forgot', ['password' => ['required', 'current_password']], ['password.current_password' => 'Password akun belum benar.']);
        $request->user()->studentProfile->update(['pin_hash' => null, 'pin_enabled' => false, 'pin_failed_attempts' => 0, 'pin_locked_until' => null]);
        $request->session()->forget(EnsureNotQuickLocked::SESSION_KEY);
        AuditLog::record('pin_reset', $request->user());

        return redirect()->route('student.profile')->with('status', 'PIN dinonaktifkan. Kamu bisa membuat PIN baru di sini.');
    }
}
