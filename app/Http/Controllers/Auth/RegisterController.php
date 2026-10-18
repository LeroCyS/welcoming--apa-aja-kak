<?php

namespace App\Http\Controllers\Auth;

use App\Actions\CompleteLoginAction;
use App\Actions\RegisterStudentAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\RegistrationOtpNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

/**
 * Pendaftaran mandiri murid: email + password, tanpa identitas sekolah. Akun baru dibuat setelah email
 * terbukti milik pendaftar lewat kode OTP. Sampai itu, data pendaftaran (password sudah di-hash) hanya
 * disimpan di sesi, sehingga email orang lain tidak bisa "diklaim" lebih dulu.
 */
class RegisterController extends Controller
{
    public const SESSION_KEY = 'registration_otp';

    public const VALID_MINUTES = 10;

    public const MAX_ATTEMPTS = 5;

    public const RESEND_COOLDOWN_SECONDS = 60;

    public const MAX_SENDS = 5;

    public function create(Request $request): View
    {
        return view('auth.register', [
            'intent' => $request->query('intent'),
            'googleEnabled' => GoogleController::isConfigured(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:191', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'intent' => ['nullable', 'string'],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Coba masuk, atau pakai "Lupa password?".',
            'password.confirmed' => 'Konfirmasi password belum sama.',
            'password.min' => 'Password minimal 8 karakter.',
        ], ['email' => 'email', 'password' => 'password']);

        $pending = [
            'email' => mb_strtolower(trim($data['email'])),
            'password' => Hash::make($data['password']),
            'intent' => $data['intent'] ?? null,
            'sends' => 0,
        ];
        if (! $this->sendCode($request, $pending)) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => 'Kode verifikasi gagal dikirim ke email itu. Periksa alamatnya, lalu coba lagi.']);
        }

        return redirect()->route('register.verify');
    }

    public function verifyForm(Request $request): View|RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);
        if (! $pending) {
            return redirect()->route('register');
        }

        return view('auth.register-verify', [
            'maskedEmail' => $this->maskEmail($pending['email']),
            'resendIn' => max(0, $pending['sent_at'] + self::RESEND_COOLDOWN_SECONDS - now()->timestamp),
            'canResend' => $pending['sends'] < self::MAX_SENDS,
            'validMinutes' => self::VALID_MINUTES,
        ]);
    }

    public function verify(Request $request, RegisterStudentAction $register, CompleteLoginAction $completeLogin): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);
        if (! $pending) {
            return redirect()->route('register')->withErrors(['email' => 'Sesi pendaftaran sudah berakhir. Isi lagi formulirnya, ya.']);
        }

        $data = $request->validate(['code' => ['required', 'digits:6']], [
            'code.required' => 'Masukkan 6 angka kode dari email.',
            'code.digits' => 'Kode terdiri dari 6 angka.',
        ]);

        if (now()->timestamp > $pending['expires_at']) {
            return back()->withErrors(['code' => 'Kode sudah kedaluwarsa. Kirim ulang kode baru, ya.']);
        }

        if (! Hash::check($data['code'], $pending['code'])) {
            $pending['attempts']++;
            if ($pending['attempts'] >= self::MAX_ATTEMPTS) {
                $request->session()->forget(self::SESSION_KEY);

                return redirect()->route('register')->withErrors(['email' => 'Kode salah terlalu banyak. Demi keamanan, ulangi pendaftaran dari awal.']);
            }
            $request->session()->put(self::SESSION_KEY, $pending);
            $left = self::MAX_ATTEMPTS - $pending['attempts'];

            return back()->withErrors(['code' => "Kode belum cocok. Sisa {$left} kali percobaan."]);
        }

        $request->session()->forget(self::SESSION_KEY);
        // Email bisa saja terdaftar oleh orang lain selama menunggu kode (mis. lewat tab lain).
        if (User::where('email', $pending['email'])->exists()) {
            return redirect()->route('login')->withErrors(['identifier' => 'Email ini sudah terdaftar. Silakan masuk.']);
        }

        // Password sudah berupa hash; cast "hashed" pada User tidak meng-hash ulang.
        $user = $register->execute(['email' => $pending['email'], 'password' => $pending['password']]);
        $request->session()->flash('status', 'Email terverifikasi dan akunmu sudah jadi. Satu langkah lagi: pilih sekolahmu.');

        return $completeLogin->execute($request, $user, false, $pending['intent']);
    }

    public function resend(Request $request): RedirectResponse
    {
        $pending = $request->session()->get(self::SESSION_KEY);
        if (! $pending) {
            return redirect()->route('register');
        }
        if ($pending['sends'] >= self::MAX_SENDS) {
            return back()->withErrors(['code' => 'Batas kirim ulang tercapai. Ulangi pendaftaran dari awal, ya.']);
        }
        $wait = $pending['sent_at'] + self::RESEND_COOLDOWN_SECONDS - now()->timestamp;
        if ($wait > 0) {
            return back()->withErrors(['code' => "Tunggu {$wait} detik sebelum kirim ulang kode."]);
        }
        if (! $this->sendCode($request, $pending)) {
            return back()->withErrors(['code' => 'Kode gagal dikirim. Coba lagi sebentar lagi, ya.']);
        }

        return back()->with('status', 'Kode baru sudah dikirim. Kode sebelumnya tidak berlaku lagi.');
    }

    /**
     * Membuat kode baru, menyimpannya (dalam bentuk hash) di sesi, lalu mengirimkannya ke email pendaftar.
     *
     * @param  array{email: string, password: string, intent: ?string, sends: int}  $pending
     */
    protected function sendCode(Request $request, array $pending): bool
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            Notification::route('mail', $pending['email'])->notify(new RegistrationOtpNotification($code, self::VALID_MINUTES));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim OTP pendaftaran', ['error' => $e->getMessage()]);

            return false;
        }

        $request->session()->put(self::SESSION_KEY, [
            ...$pending,
            'code' => Hash::make($code),
            'attempts' => 0,
            'sends' => $pending['sends'] + 1,
            'sent_at' => now()->timestamp,
            'expires_at' => now()->addMinutes(self::VALID_MINUTES)->timestamp,
        ]);

        return true;
    }

    /** "alya.putri@gmail.com" menjadi "al********@gmail.com". */
    protected function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email, 2) + [1 => ''];

        return Str::substr($local, 0, 2).str_repeat('*', max(3, Str::length($local) - 2)).'@'.$domain;
    }
}
