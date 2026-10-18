<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SchoolTransferRequest;
use App\Models\User;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Murid meminta admin sekolah melepaskannya (pindah / salah pilih sekolah), atau membatalkan permintaannya. */
class SchoolTransferController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validateWithBag(
            'transfer',
            ['reason' => ['required', Rule::in(array_keys(SchoolTransferRequest::REASONS))]],
            ['reason.required' => 'Pilih alasannya dulu.', 'reason.in' => 'Pilih salah satu alasan yang tersedia.'],
        );

        $pending = SchoolTransferRequest::pending()->where('student_id', $user->id)->where('school_id', $user->school_id)->exists();
        if ($pending) {
            return back()->with('status', 'Permintaanmu masih ditinjau admin sekolah.');
        }

        $transfer = SchoolTransferRequest::create(['student_id' => $user->id, 'school_id' => $user->school_id, 'reason' => $data['reason']]);
        AuditLog::record('school_transfer_requested', $transfer);

        User::where('role', User::ROLE_ADMIN)->inSchool($user->school_id)->active()->get()
            ->each(fn (User $admin) => Notify::user($admin, 'school_transfer', 'Murid #'.$user->id.' minta pindah sekolah', route('admin.accounts').'#pindah-sekolah', false, $transfer->reasonLabel()));

        return redirect(route('student.profile').'#sekolah')->with('status', 'Permintaan terkirim. Admin sekolah akan meninjaunya.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();
        SchoolTransferRequest::pending()->where('student_id', $user->id)->where('school_id', $user->school_id)
            ->update(['status' => 'cancelled', 'handled_at' => now()]);

        return redirect(route('student.profile').'#sekolah')->with('status', 'Permintaan pindah sekolah dibatalkan.');
    }
}
