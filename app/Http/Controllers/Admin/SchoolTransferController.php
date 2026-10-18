<?php

namespace App\Http\Controllers\Admin;

use App\Actions\HandleSchoolTransferRequestAction;
use App\Http\Controllers\Controller;
use App\Models\SchoolTransferRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Admin meninjau permintaan pindah sekolah dari murid di sekolahnya. */
class SchoolTransferController extends Controller
{
    public function approve(Request $request, SchoolTransferRequest $transfer, HandleSchoolTransferRequestAction $action): RedirectResponse
    {
        $this->ensurePendingInSchool($request, $transfer);
        $action->approve($transfer, $request->user());

        return back()->with('status', "Murid #{$transfer->student_id} dilepaskan dari sekolah. Ia akan memilih sekolah lagi saat masuk.");
    }

    public function reject(Request $request, SchoolTransferRequest $transfer, HandleSchoolTransferRequestAction $action): RedirectResponse
    {
        $this->ensurePendingInSchool($request, $transfer);
        $action->reject($transfer, $request->user());

        return back()->with('status', "Permintaan Murid #{$transfer->student_id} ditolak.");
    }

    protected function ensurePendingInSchool(Request $request, SchoolTransferRequest $transfer): void
    {
        abort_unless((int) $transfer->school_id === (int) $request->user()->school_id, 404);
        abort_unless($transfer->status === 'pending', 409, 'Permintaan ini sudah ditangani.');
    }
}
