<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatPresenter;
use App\Services\MessageEditor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Edit / hapus pesan (murid & Guru BK). Setiap aksi membalas dengan isi chat terbaru (full). */
class ChatMessageController extends Controller
{
    public function __construct(protected MessageEditor $editor, protected ChatPresenter $presenter) {}

    public function update(Request $request, Conversation $conversation, Message $message): JsonResponse
    {
        $this->authorize('reply', $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']], [
            'body.required' => 'Pesannya masih kosong.',
            'body.max' => 'Pesan maksimal 2000 karakter.',
        ]);
        $this->editor->edit($message, $request->user(), trim($data['body']));

        return $this->refreshed($request, $conversation);
    }

    /** scope=me: hapus untuk saya · scope=all: hapus untuk semua orang. */
    public function destroy(Request $request, Conversation $conversation, Message $message): JsonResponse
    {
        $scope = $request->validate(['scope' => ['required', 'in:me,all']])['scope'];
        if ($scope === 'all') {
            $this->authorize('reply', $conversation);
            $this->editor->retract($message, $request->user());
        } else {
            $this->authorize('view', $conversation);
            $this->editor->hideFor($message, $request->user());
        }

        return $this->refreshed($request, $conversation);
    }

    protected function refreshed(Request $request, Conversation $conversation): JsonResponse
    {
        $request->merge(['full' => 1]);

        return response()->json($this->presenter->payload($request, $conversation->fresh()));
    }
}
