<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Membuat akun murid baru tanpa identitas apa pun (tanpa NISN, nama, kelas) dan belum terikat sekolah.
 * Nama/kelas/tingkat opsional dan hanya diisi murid sendiri di profil.
 */
class RegisterStudentAction
{
    /** @param  array{email: string|null, password?: string|null, google_id?: string|null}  $data */
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                // Sekolah dipilih murid sendiri saat onboarding (jenjang → sekolah → Guru BK).
                'school_id' => null,
                'role' => User::ROLE_STUDENT,
                'name' => null,
                'identifier' => null,
                'email' => $data['email'],
                'google_id' => $data['google_id'] ?? null,
                'password' => $data['password'] ?? null,
                'must_change_password' => false,
                'is_active' => true,
            ]);
            StudentProfile::create(['user_id' => $user->id]);
            AuditLog::record('account_registered', $user, ['via' => isset($data['google_id']) ? 'google' : 'email'], $user->id);

            return $user;
        });
    }
}
