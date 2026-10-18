<?php

namespace App\Http\Controllers\Auth;

use App\Actions\StartEmergencyConversationAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(Request $request): View
    {
        return view('auth.change-password', ['forced' => $request->user()->must_change_password]);
    }

    public function update(Request $request, StartEmergencyConversationAction $emergency): RedirectResponse
    {
        $user = $request->user();
        $request->validate([
            'current_password' => [$user->must_change_password ? 'nullable' : 'required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8), function ($attr, $value, $fail) use ($user) {
                if ($user->identifier !== null && mb_strtolower($value) === mb_strtolower($user->identifier)) {
                    $fail('Password baru tidak boleh sama dengan NIP/kode akun.');
                }
            }],
        ], [
            'password.confirmed' => 'Konfirmasi password belum sama.',
            'password.min' => 'Password minimal 8 karakter.',
            'current_password.current_password' => 'Password lama belum benar.',
        ], ['password' => 'password baru', 'current_password' => 'password lama']);

        $user->forceFill(['password' => $request->input('password'), 'must_change_password' => false])->save();

        if ($request->session()->pull('login_intent') === 'darurat' && $user->isStudent()) {
            return redirect()->route('student.conversations.show', $emergency->execute($user));
        }

        return redirect()->intended($user->homeRoute())->with('status', 'Password berhasil diganti. Selamat datang!');
    }
}
