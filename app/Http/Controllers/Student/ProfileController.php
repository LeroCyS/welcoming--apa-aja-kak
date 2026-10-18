<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CounselingSession;
use App\Models\SchoolTransferRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load('studentProfile', 'school');

        return view('student.profile', [
            'user' => $user,
            'profile' => $user->studentProfile,
            'upcoming' => CounselingSession::where('student_id', $user->id)->where('status', 'scheduled')->count(),
            'counselorCount' => User::counselors()->inSchool($user->school_id)->active()->count(),
            // Permintaan pindah sekolah terakhir di sekolah ini (menunggu, atau ditolak dalam 14 hari terakhir).
            'transfer' => SchoolTransferRequest::where('student_id', $user->id)->where('school_id', $user->school_id)
                ->where(fn ($q) => $q->where('status', 'pending')->orWhere(fn ($r) => $r->where('status', 'rejected')->where('handled_at', '>=', now()->subDays(14))))
                ->latest('id')->first(),
        ]);
    }

    public function settings(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'notifications_enabled' => ['sometimes', 'boolean'],
            'mood_prompt_enabled' => ['sometimes', 'boolean'],
            'mood_signal_enabled' => ['sometimes', 'boolean'],
        ]);
        $request->user()->studentProfile->update($data);

        return $request->expectsJson()
            ? response()->json(['ok' => true])
            : back()->with('status', 'Pengaturan tersimpan.');
    }

    /** Identitas diisi murid sendiri (semua opsional) dan hanya terlihat guru bila murid membuka identitasnya. */
    public function identity(Request $request): RedirectResponse
    {
        [$minGrade, $maxGrade] = $request->user()->school->gradeRange();
        $data = $request->validateWithBag('identity', [
            'name' => ['nullable', 'string', 'max:120'],
            'class_name' => ['nullable', 'string', 'max:40'],
            'grade_level' => ['nullable', 'integer', "between:{$minGrade},{$maxGrade}"],
        ], [
            'grade_level.between' => "Tingkat untuk {$request->user()->school->levelLabel()} diisi angka {$minGrade} sampai {$maxGrade}.",
        ], ['name' => 'nama', 'class_name' => 'kelas', 'grade_level' => 'tingkat']);

        $user = $request->user();
        $user->forceFill(['name' => filled($data['name'] ?? null) ? trim($data['name']) : null])->save();
        $user->studentProfile->update([
            'class_name' => filled($data['class_name'] ?? null) ? trim($data['class_name']) : null,
            'grade_level' => $data['grade_level'] ?? null,
        ]);

        return back()->with('status', 'Profil tersimpan.');
    }

    public function password(Request $request): RedirectResponse
    {
        $user = $request->user();
        // Akun Google tidak menyimpan email, jadi password tidak bisa dipakai untuk masuk.
        abort_if($user->email === null, 403);
        // Akun lama yang tersambung Google tapi belum punya password: boleh langsung membuat password tanpa password lama.
        $request->validateWithBag('password', [
            'current_password' => [$user->password === null ? 'nullable' : 'required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Password lama belum benar.',
            'password.confirmed' => 'Konfirmasi password belum sama.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);
        $user->forceFill(['password' => $request->input('password')])->save();

        return back()->with('status', 'Password berhasil diganti.');
    }
}
