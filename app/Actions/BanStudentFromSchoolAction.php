<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\SchoolBan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Admin mengeluarkan murid pembuat laporan iseng dari sekolahnya. Admin hanya memegang percakapan
 * (tanpa isi & identitas); murid dicari lewat student_hash dan tetap bebas memilih sekolah lain.
 */
class BanStudentFromSchoolAction
{
    public function __construct(protected JoinSchoolAction $membership) {}

    public function execute(Conversation $conversation, User $admin): void
    {
        DB::transaction(function () use ($conversation, $admin) {
            SchoolBan::firstOrCreate(
                ['school_id' => $conversation->school_id, 'student_hash' => $conversation->student_hash],
                ['banned_by' => $admin->id],
            );

            $student = User::find((int) $conversation->student_ref);
            if ($student && (int) $student->school_id === (int) $conversation->school_id) {
                $this->membership->leave($student);
            }

            // Percakapan lain dari murid yang sama di sekolah ini juga diarsipkan (mis. bila ia sudah pindah lebih dulu).
            Conversation::inSchool($conversation->school_id)->where('student_hash', $conversation->student_hash)
                ->whereNull('student_left_at')->update(['student_left_at' => now()]);

            AuditLog::record('student_banned_from_school', $conversation);
        });
    }
}
