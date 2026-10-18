<?php

namespace App\Http\Controllers\Student;

use App\Actions\AcceptInvitationAction;
use App\Http\Controllers\Controller;
use App\Models\SessionInvitation;
use App\Services\ConversationMessenger;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InvitationController extends Controller
{
    public function accept(Request $request, SessionInvitation $invitation, AcceptInvitationAction $accept): RedirectResponse
    {
        $this->authorize('reply', $invitation->conversation);
        $data = $request->validate(['slot_id' => ['required', 'integer']], ['slot_id.required' => 'Pilih salah satu jadwal dulu, ya.']);
        $accept->execute($invitation, $request->user(), (int) $data['slot_id'], $request->boolean('confirm_reveal'));

        return redirect()->route('student.conversations.show', $invitation->conversation)->with('status', 'Jadwal tersimpan! Detailnya ada di Jadwal Saya.');
    }

    public function decline(Request $request, SessionInvitation $invitation, ConversationMessenger $messenger): RedirectResponse
    {
        $c = $invitation->conversation;
        $this->authorize('reply', $c);
        if ($invitation->status === 'pending') {
            $invitation->update(['status' => 'declined', 'responded_at' => now()]);
            $messenger->system($c, 'Kamu memilih belum siap untuk bertemu. Nggak apa-apa, ceritanya tetap bisa lanjut di sini.');
            Notify::counselor($c, 'invitation_declined', 'Undangan sesi: murid belum siap bertemu');
            Realtime::conversation($c->uuid, 'ConversationUpdated', ['invitation' => $invitation->id]);
        }

        return redirect()->route('student.conversations.show', $c)->with('status', 'Nggak apa-apa. Kamu bisa minta jadwal kapan pun kamu siap.');
    }
}
