<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

/** Lupa password murid lewat email (akun murid tidak dikelola admin, jadi admin tidak bisa mereset). */
class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email']], [], ['email' => 'email']);
        Password::sendResetLink(['email' => mb_strtolower(trim($data['email'])), 'role' => User::ROLE_STUDENT]);

        // Pesan sama untuk email terdaftar/tidak, agar tidak bisa dipakai menebak siapa yang punya akun.
        return back()->with('status', 'Kalau email itu terdaftar, tautan untuk membuat password baru sudah kami kirim. Cek kotak masuk atau folder spam, ya.');
    }

    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => (string) $request->query('email')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ], [
            'password.confirmed' => 'Konfirmasi password belum sama.',
            'password.min' => 'Password minimal 8 karakter.',
        ], ['email' => 'email', 'password' => 'password baru']);

        $status = Password::reset(
            ['email' => mb_strtolower(trim($data['email'])), 'role' => User::ROLE_STUDENT, 'password' => $data['password'], 'token' => $data['token']],
            function (User $user, string $password) {
                $user->forceFill(['password' => $password, 'must_change_password' => false, 'remember_token' => Str::random(60)])->save();
                AuditLog::record('password_reset_self', $user, [], $user->id);
                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Tautan ini sudah tidak berlaku atau emailnya tidak cocok. Minta tautan baru, ya.']);
        }

        return redirect()->route('login')->with('status', 'Password baru tersimpan. Silakan masuk.');
    }
}
