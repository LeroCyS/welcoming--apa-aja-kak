<?php

namespace App\Http\Controllers\Auth;

use App\Actions\CompleteLoginAction;
use App\Actions\RegisterStudentAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\IdentityHasher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

/**
 * Masuk/daftar murid dengan Google. Hanya izin `openid` yang diminta, jadi Google hanya mengirim ID unik akun:
 * tanpa email, nama, atau foto. ID itu pun disimpan sebagai HMAC (lihat googleHash()), bukan ID mentah.
 */
class GoogleController extends Controller
{
    public static function isConfigured(): bool
    {
        return filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
    }

    /** Penanda akun Google yang disimpan di users.google_id; ID asli Google tidak pernah disimpan. */
    public static function googleHash(string $googleId): string
    {
        return IdentityHasher::hash('google:'.$googleId);
    }

    public function redirect(Request $request): SymfonyRedirectResponse
    {
        abort_unless(static::isConfigured(), 404);
        if ($intent = $request->query('intent')) {
            $request->session()->put('login_intent', $intent);
        }

        // setScopes() mengganti izin bawaan Socialite (openid, profile, email); scopes() hanya menambah.
        return Socialite::driver('google')->setScopes(['openid'])->redirect();
    }

    public function callback(Request $request, RegisterStudentAction $register, CompleteLoginAction $completeLogin): RedirectResponse
    {
        abort_unless(static::isConfigured(), 404);
        $intent = $request->session()->pull('login_intent');

        try {
            $googleId = (string) Socialite::driver('google')->user()->getId();
        } catch (Throwable) {
            return $this->fail('Masuk dengan Google dibatalkan atau gagal. Coba lagi, ya.');
        }
        if ($googleId === '') {
            return $this->fail('Masuk dengan Google gagal. Coba lagi, ya.');
        }

        $googleHash = static::googleHash($googleId);
        $user = User::where('google_id', $googleHash)->first();

        if ($user && ! $user->isStudent()) {
            return $this->fail('Masuk dengan Google hanya untuk murid. Guru BK dan admin masuk memakai email/NIP dan password.');
        }
        if ($user && ! $user->is_active) {
            return $this->fail('Akunmu sedang dinonaktifkan. Hubungi admin sekolah.');
        }

        if (! $user) {
            $user = $register->execute(['email' => null, 'google_id' => $googleHash]);
        }

        return $completeLogin->execute($request, $user, true, $intent);
    }

    protected function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['identifier' => $message]);
    }
}
