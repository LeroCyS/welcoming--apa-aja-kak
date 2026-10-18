<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\CounselingSession;
use App\Models\School;
use App\Models\SchoolTransferRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Murid bergabung ke sekolah (pertama kali atau pindah). Saat keluar dari sekolah lama,
 * percakapan di sana menjadi arsip baca-saja dan jadwal konseling yang belum berjalan dibatalkan.
 */
class JoinSchoolAction
{
    public function execute(User $student, School $school): void
    {
        DB::transaction(function () use ($student, $school) {
            $this->leave($student);
            $student->forceFill(['school_id' => $school->id, 'school_joined_at' => now()])->save();
            $student->setRelation('school', $school);

            // Tingkat kelas dari jenjang lama (mis. kelas 11 SMA) tidak berlaku di jenjang baru.
            [$minGrade, $maxGrade] = $school->gradeRange();
            $grade = $student->studentProfile?->grade_level;
            if ($grade !== null && ($grade < $minGrade || $grade > $maxGrade)) {
                $student->studentProfile->update(['grade_level' => null]);
            }
            AuditLog::record('school_joined', $school, [], $student->id);
        });
    }

    public function leave(User $student): void
    {
        if ($student->school_id === null) {
            return;
        }

        Conversation::forStudent($student)->inSchool($student->school_id)->whereNull('student_left_at')
            ->update(['student_left_at' => now()]);
        CounselingSession::where('student_id', $student->id)->where('status', 'scheduled')
            ->whereIn('counselor_id', User::counselors()->inSchool($student->school_id)->select('id'))
            ->update(['status' => 'cancelled']);

        // Dilepaskan lewat tombol "Lepaskan" admin juga menuntaskan permintaan pindah yang masih menunggu.
        SchoolTransferRequest::pending()->where('student_id', $student->id)->where('school_id', $student->school_id)
            ->update(['status' => 'approved', 'handled_at' => now()]);

        $student->forceFill(['school_id' => null, 'school_joined_at' => null])->save();
        $student->unsetRelation('school');
    }
}
