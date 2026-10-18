<?php

namespace App\Http\Controllers\Auth;

use App\Actions\CompleteLoginAction;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(Request $request): View
    {
        $role = $request->query('peran', 'student');

        return view('auth.login', [
            'role' => in_array($role, ['student', 'counselor', 'admin'], true) ? $role : 'student',
            'intent' => $request->query('intent'),
            'googleEnabled' => GoogleController::isConfigured(),
        ]);
    }

    public function store(Request $request, CompleteLoginAction $completeLogin): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['student', 'counselor', 'admin'])],
            'identifier' => ['required', 'string', 'max:191'],
            'password' => ['required', 'string'],
            'intent' => ['nullable', 'string'],
        ], [], ['identifier' => 'email atau NIP', 'password' => 'password']);

        $id = trim($data['identifier']);
        $user = User::where('identifier', $id)->orWhere('email', $id)->first();

        if ($user && $user->password === null) {
            return back()->withInput($request->except('password'))
                ->withErrors(['identifier' => 'Akun ini dibuat lewat Google. Pakai tombol "Masuk dengan Google", atau buat password lewat "Lupa password?".']);
        }
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()->withInput($request->except('password'))
                ->withErrors(['identifier' => 'Email/NIP atau password belum cocok. Coba periksa lagi, ya.']);
        }
        if (! $user->is_active) {
            return back()->withInput($request->except('password'))
                ->withErrors(['identifier' => 'Akunmu sedang dinonaktifkan. Hubungi admin sekolah.']);
        }
        if ($user->role !== $data['role']) {
            $label = ['student' => 'Murid', 'counselor' => 'Guru BK', 'admin' => 'Admin'][$user->role];

            return back()->withInput($request->except('password'))
                ->withErrors(['role' => "Akun ini terdaftar sebagai {$label}. Pilih kartu peran \"{$label}\" lalu coba lagi, ya."]);
        }

        return $completeLogin->execute($request, $user, $request->boolean('remember'), $data['intent'] ?? null);
    }

    /** Keluar Akun biasa: logout penuh, dan perangkat ini berhenti menerima notifikasi akun tersebut. */
    public function destroy(Request $request): RedirectResponse
    {
        if (filled($endpoint = $request->input('push_endpoint'))) {
            $request->user()?->deletePushSubscription((string) $endpoint);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Kamu sudah keluar. Sampai jumpa lagi!');
    }
}
