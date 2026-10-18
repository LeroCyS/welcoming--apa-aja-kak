<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\SchoolTransferRequest;
use App\Models\User;
use App\Support\Notify;
use Illuminate\Support\Facades\DB;

/**
 * Admin menyetujui atau menolak permintaan pindah sekolah. Disetujui = murid dilepaskan seperti tombol "Lepaskan":
 * cerita di sekolah ini menjadi arsip baca-saja dan murid memilih sekolah lagi saat masuk berikutnya.
 */
class HandleSchoolTransferRequestAction
{
    public function __construct(protected JoinSchoolAction $membership) {}

    public function approve(SchoolTransferRequest $request, User $admin): void
    {
        DB::transaction(function () use ($request, $admin) {
            $request->forceFill(['status' => 'approved', 'handled_by' => $admin->id, 'handled_at' => now()])->save();

            $student = $request->student;
            if ($student && (int) $student->school_id === (int) $request->school_id) {
                $this->membership->leave($student);
            }
            AuditLog::record('school_transfer_approved', $request);
        });

        Notify::user($request->student, 'school_transfer', 'Permintaan pindah sekolahmu disetujui', route('student.onboarding'), false, 'Pilih sekolahmu yang baru saat masuk.');
    }

    public function reject(SchoolTransferRequest $request, User $admin): void
    {
        $request->forceFill(['status' => 'rejected', 'handled_by' => $admin->id, 'handled_at' => now()])->save();
        AuditLog::record('school_transfer_rejected', $request);

        Notify::user($request->student, 'school_transfer', 'Permintaan pindah sekolahmu belum disetujui', route('student.profile').'#sekolah', false, 'Hubungi admin sekolahmu bila ada yang perlu dijelaskan.');
    }
}
