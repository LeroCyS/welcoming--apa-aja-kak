<?php

namespace App\Http\Controllers\Student;

use App\Actions\RevealIdentityAction;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\IdentityRequest;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class IdentityController extends Controller
{
    /** Inisiatif murid: tombol "Buka Identitas". */
    public function reveal(Request $request, Conversation $conversation, RevealIdentityAction $reveal): RedirectResponse
    {
        $this->authorize('reply', $conversation);
        $request->validate(['confirm' => ['accepted']], ['confirm.accepted' => 'Konfirmasi dulu sebelum membuka identitas.']);
        $reveal->execute($conversation, $request->user());
        $conversation->identityRequests()->where('status', 'pending')->where('counselor_id', $conversation->counselor_id)
            ->update(['status' => 'accepted', 'responded_at' => now()]);

        return redirect()->route('student.conversations.show', $conversation)->with('status', 'Identitasmu sudah terbuka untuk '.$conversation->counselor->displayName().'.');
    }

    /** Menjawab "Permintaan Buka Identitas" dari guru. */
    public function respond(Request $request, IdentityRequest $identityRequest, RevealIdentityAction $reveal): RedirectResponse
    {
        $c = $identityRequest->conversation;
        $this->authorize('reply', $c);
        $data = $request->validate(['response' => ['required', Rule::in(['accepted', 'declined'])]]);
        abort_unless($identityRequest->status === 'pending', 409, 'Permintaan ini sudah dijawab.');

        if ($data['response'] === 'accepted') {
            $request->validate(['confirm' => ['accepted']], ['confirm.accepted' => 'Konfirmasi dulu sebelum membuka identitas.']);
            $reveal->execute($c, $request->user());
            $identityRequest->update(['status' => 'accepted', 'responded_at' => now()]);
            $msg = 'Identitasmu sudah terbuka untuk '.$c->counselor->displayName().'.';
        } else {
            $identityRequest->update(['status' => 'declined', 'responded_at' => now()]);
            Notify::counselor($c, 'identity_declined', 'Murid memilih tetap anonim');
            Realtime::conversation($c->uuid, 'ConversationUpdated', ['identity' => 'declined']);
            $msg = 'Kamu tetap anonim. Nggak apa-apa, ceritanya tetap bisa lanjut.';
        }

        return redirect()->route('student.conversations.show', $c)->with('status', $msg);
    }
}
