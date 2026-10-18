<?php

namespace App\Http\Controllers\Admin;

use App\Actions\BanStudentFromSchoolAction;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Admin menindaklanjuti laporan iseng dari Guru BK tanpa melihat isi cerita atau identitas murid. */
class PrankReportController extends Controller
{
    public function ban(Request $request, Conversation $conversation, BanStudentFromSchoolAction $ban): RedirectResponse
    {
        $this->ensureSameSchool($conversation);
        abort_if($conversation->prank_reported_at === null, 404);

        $ban->execute($conversation, $request->user());

        return redirect()->route('admin.settings', ['tab' => 'laporan-iseng'])
            ->with('status', 'Murid '.$conversation->anonymous_alias.' dikeluarkan dan tidak bisa bergabung lagi ke sekolah ini.');
    }
}
