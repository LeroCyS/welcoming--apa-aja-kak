<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Langkah bersama setelah kredensial valid: login, catat, lalu arahkan (termasuk intent darurat). */
class CompleteLoginAction
{
    public function __construct(protected StartEmergencyConversationAction $emergency) {}

    public function execute(Request $request, User $user, bool $remember = false, ?string $intent = null): RedirectResponse
    {
        $intended = $request->session()->get('url.intended');
        if ($intended && ! $user->ownsUrl($intended)) {
            $request->session()->forget('url.intended');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        AuditLog::record('login', $user, [], $user->id);

        if ($user->must_change_password) {
            if ($intent) {
                $request->session()->put('login_intent', $intent);
            }

            return redirect()->route('password.change');
        }

        // Murid baru / dilepas dari sekolah: onboarding dulu (belum ada Guru BK yang bisa dihubungi), kontak darurat tetap terbuka.
        if ($user->needsOnboarding()) {
            return redirect()->route('student.onboarding')->with('emergency', $intent === 'darurat');
        }

        if ($intent === 'darurat' && $user->isStudent()) {
            return redirect()->route('student.conversations.show', $this->emergency->execute($user));
        }

        return redirect()->intended($user->homeRoute());
    }
}
