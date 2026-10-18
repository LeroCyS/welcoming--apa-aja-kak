<?php

namespace App\Http\Controllers\Counselor;

use App\Http\Controllers\Controller;
use App\Models\CaseNote;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Catatan privat: hanya terlihat & bisa diubah oleh pembuatnya. */
class CaseNoteController extends Controller
{
    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorize('counsel', $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']], ['body.required' => 'Catatan masih kosong.']);
        CaseNote::create(['conversation_id' => $conversation->id, 'counselor_id' => $request->user()->id, 'body' => $data['body']]);

        return redirect()->to(route('counselor.conversations.show', $conversation).'?tab=catatan')->with('status', 'Catatan privat tersimpan.');
    }

    public function update(Request $request, CaseNote $caseNote): RedirectResponse
    {
        $this->authorize('update', $caseNote);
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $caseNote->update($data);

        return redirect()->to(route('counselor.conversations.show', $caseNote->conversation).'?tab=catatan')->with('status', 'Catatan diperbarui.');
    }

    public function destroy(CaseNote $caseNote): RedirectResponse
    {
        $this->authorize('delete', $caseNote);
        $c = $caseNote->conversation;
        $caseNote->delete();

        return redirect()->to(route('counselor.conversations.show', $c).'?tab=catatan')->with('status', 'Catatan dihapus.');
    }
}
