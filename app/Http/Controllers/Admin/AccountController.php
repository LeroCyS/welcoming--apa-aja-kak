<?php

namespace App\Http\Controllers\Admin;

use App\Actions\JoinSchoolAction;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\CounselorProfile;
use App\Models\CounselorServiceHour;
use App\Models\School;
use App\Models\SchoolTransferRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'guru' ? 'guru' : 'murid';
        $q = trim((string) $request->query('q'));
        $schoolId = $request->user()->school_id;

        // Murid mendaftar sendiri secara anonim: admin hanya melihat nomor akun & status, tanpa nama/email,
        // dan tidak bisa mencari murid berdasarkan identitas apa pun.
        $query = $tab === 'guru'
            ? User::counselors()->inSchool($schoolId)->with('counselorProfile')
                ->when($q !== '', fn ($w) => $w->where(fn ($x) => $x->where('name', 'like', "%{$q}%")->orWhere('identifier', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
                ->orderBy('name')
            : User::students()->inSchool($schoolId)
                // Murid bisa memberi tahu nomor akunnya ("Murid #123") saat minta dilepaskan dari sekolah.
                ->when($q !== '', fn ($w) => $w->whereKey((int) ltrim($q, '#')))
                ->latest('id');

        return view('admin.accounts', [
            'tab' => $tab,
            'users' => $query->paginate(20)->withQueryString(),
            'transfers' => SchoolTransferRequest::pending()->where('school_id', $schoolId)->oldest()->get(),
            'counts' => [
                'murid' => User::students()->inSchool($schoolId)->count(),
                'guru' => User::counselors()->inSchool($schoolId)->count(),
                'total' => User::inSchool($schoolId)->count(),
                'pending' => User::inSchool($schoolId)->where('must_change_password', true)->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'identifier' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,identifier'],
            'email' => ['nullable', 'email', 'max:191', 'unique:users,email'],
            'nickname' => ['required', 'string', 'max:60'],
        ], [
            'identifier.unique' => 'NIP/kode ini sudah terdaftar.',
            'identifier.regex' => 'NIP/kode hanya boleh berisi huruf, angka, titik, atau strip.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'nickname.required' => 'Nama panggilan wajib diisi untuk Guru BK.',
        ], ['name' => 'nama', 'identifier' => 'NIP/kode']);

        $user = DB::transaction(function () use ($data) {
            $school = School::current();
            $user = User::create([
                'school_id' => $school->id,
                'role' => User::ROLE_COUNSELOR,
                'name' => $data['name'],
                'identifier' => $data['identifier'],
                'email' => $data['email'] ?? null,
                'password' => $data['identifier'],
                'must_change_password' => true,
            ]);
            CounselorProfile::create(['user_id' => $user->id, 'nickname' => $data['nickname'], 'offline_auto_reply' => null]);
            foreach (range(1, 5) as $d) {
                CounselorServiceHour::create(['counselor_id' => $user->id, 'day_of_week' => $d, 'start_time' => $school->default_service_start, 'end_time' => $school->default_service_end]);
            }

            return $user;
        });
        AuditLog::record('account_created', $user, ['role' => $user->role]);

        return redirect()->route('admin.accounts', ['tab' => 'guru'])
            ->with('status', "Akun {$user->name} dibuat. Password awal = NIP/kode, wajib diganti saat pertama masuk.");
    }

    public function resetPassword(User $user): RedirectResponse
    {
        // Murid mereset sendiri lewat "Lupa password?" (email); admin tidak memegang kredensial murid.
        $this->ensureSameSchool($user);
        abort_unless($user->isCounselor(), 403);
        $user->forceFill(['password' => $user->identifier, 'must_change_password' => true, 'remember_token' => null])->save();
        AuditLog::record('password_reset', $user);

        return back()->with('status', "Password {$user->name} direset ke NIP/kode. Ia akan diminta menggantinya saat masuk.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        $this->ensureSameSchool($user);
        abort_if($user->isAdmin(), 403);
        $user->forceFill(['is_active' => ! $user->is_active])->save();
        AuditLog::record($user->is_active ? 'account_activated' : 'account_deactivated', $user);

        $label = $user->isStudent() ? 'Murid #'.$user->id : $user->name;

        return back()->with('status', $user->is_active ? "Akun {$label} diaktifkan kembali." : "Akun {$label} dinonaktifkan.");
    }

    /**
     * Murid tidak bisa mengganti sekolahnya sendiri. Bila salah pilih atau pindah sekolah, admin melepaskannya:
     * cerita di sekolah ini menjadi arsip baca-saja dan murid memilih sekolah lagi saat masuk berikutnya.
     */
    public function releaseFromSchool(User $user, JoinSchoolAction $membership): RedirectResponse
    {
        $this->ensureSameSchool($user);
        abort_unless($user->isStudent(), 403);
        $membership->leave($user);
        AuditLog::record('student_released_from_school', $user);

        return back()->with('status', "Murid #{$user->id} dilepaskan dari sekolah. Ia akan diminta memilih sekolah lagi saat masuk.");
    }
}
