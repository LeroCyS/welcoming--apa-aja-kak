<?php

namespace App\Http\Controllers\Student;

use App\Actions\JoinSchoolAction;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Support\IdentityHasher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Onboarding murid setelah register (akun cukup email + password): tutorial cara kerja → jenjang → sekolah →
 * Guru BK (opsional), lalu walkthrough di Beranda. Nama, PIN, dan notifikasi diatur sendiri di Profil.
 * Sekolah dipilih sendiri tanpa kode dan tidak bisa diganti murid; bila keliru, admin sekolah melepaskannya
 * dan murid kembali ke langkah jenjang di sini.
 */
class OnboardingController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $user = $request->user()->load('studentProfile');
        if (! $user->needsOnboarding()) {
            return redirect()->route('student.home');
        }

        return view('student.onboarding', [
            'user' => $user,
            'firstTime' => $user->studentProfile?->onboarded_at === null,
            'levels' => collect(School::LEVELS)->map(fn ($level) => $level['label']),
            'schools' => School::withCount(['users as counselor_count' => fn ($q) => $q->where('role', User::ROLE_COUNSELOR)->where('is_active', true)])
                ->orderBy('name')->get(['id', 'name', 'level', 'address'])
                ->map(fn (School $s) => ['id' => $s->id, 'name' => $s->name, 'level' => $s->level, 'address' => $s->address, 'counselors' => $s->counselor_count])->values(),
        ]);
    }

    /** Daftar Guru BK aktif di sekolah yang dipilih (info yang sama dengan halaman "Kenalan Guru BK"). */
    public function counselors(Request $request, AvailabilityService $availability): JsonResponse
    {
        $request->validate(['school_id' => ['required', 'integer', 'exists:schools,id']]);

        return response()->json(User::counselors()->inSchool($request->integer('school_id'))->active()->with(['counselorProfile', 'serviceHours'])->orderBy('id')->get()
            ->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->displayName(),
                'full' => $u->name,
                'initial' => $u->initial(),
                'photo' => $u->counselorProfile?->photoUrl(),
                'intro' => $u->counselorProfile?->intro_message,
                'online' => $availability->isOnline($u),
                'hours' => $availability->summary($u),
            ])->values());
    }

    public function store(Request $request, JoinSchoolAction $join): RedirectResponse
    {
        $user = $request->user()->load('studentProfile');
        // Sekolah tidak bisa diganti murid sendiri: hanya admin yang bisa melepaskannya.
        if (! $user->needsOnboarding()) {
            return redirect()->route('student.home');
        }
        $profile = $user->studentProfile;

        $data = $request->validate([
            'level' => ['required', Rule::in(array_keys(School::LEVELS))],
            'school_id' => ['required', 'integer', Rule::exists('schools', 'id')->where('level', $request->input('level'))],
            'counselor_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', User::ROLE_COUNSELOR)->where('is_active', true)->where('school_id', $request->input('school_id'))],
        ], [
            'level.required' => 'Pilih jenjang pendidikanmu dulu.',
            'school_id.required' => 'Pilih sekolahmu.',
            'school_id.exists' => 'Sekolah itu tidak ada di jenjang yang kamu pilih.',
            'counselor_id.exists' => 'Guru BK itu tidak ada di sekolah yang kamu pilih.',
        ]);

        $school = School::findOrFail($data['school_id']);
        if ($school->isBanned(IdentityHasher::hash($user->id))) {
            return back()->withInput()
                ->withErrors(['school_id' => 'Akunmu tidak bisa bergabung ke sekolah ini. Pilih sekolah lain, atau hubungi Guru BK sekolahmu secara langsung bila ini keliru.']);
        }

        $join->execute($user, $school);
        $profile->onboarded_at ??= now();
        $profile->save();

        $welcome = 'Selamat datang di ruang BK '.$school->name.'. Identitasmu tetap rahasia.';
        if (! empty($data['counselor_id']) && ! $request->boolean('skip_counselor')) {
            return redirect()->route('student.conversations.create', ['guru' => $data['counselor_id']])->with('status', $welcome.' Yuk, pilih topik ceritamu.');
        }

        return redirect()->route('student.home')->with('status', $welcome);
    }

    /** Walkthrough Beranda selesai atau dilewati: tidak ditampilkan lagi. */
    public function completeTour(Request $request): JsonResponse
    {
        $request->user()->studentProfile->forceFill(['tour_completed_at' => now()])->save();

        return response()->json(['ok' => true]);
    }
}
