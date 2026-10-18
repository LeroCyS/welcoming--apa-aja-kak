<?php

namespace App\Http\Controllers\Student;

use App\Actions\SendStudentMessageAction;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Services\ChatPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(protected ChatPresenter $presenter) {}

    /** Polling / refresh: GET ?after={id} atau ?full=1 */
    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('own', $conversation);

        return response()->json($this->payload($request, $conversation));
    }

    public function store(Request $request, Conversation $conversation, SendStudentMessageAction $send): JsonResponse
    {
        $this->authorize('reply', $conversation);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']], [
            'body.required' => 'Pesannya masih kosong.',
            'body.max' => 'Pesan maksimal 2000 karakter.',
        ]);
        $send->text($conversation, trim($data['body']));

        return response()->json($this->payload($request, $conversation->fresh()));
    }

    public function payload(Request $request, Conversation $c): array
    {
        return $this->presenter->payload($request, $c);
    }

    /** Murid sedang mengetik: dikabarkan ke guru agar terlihat langsung. */
    public function typing(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('reply', $conversation);
        $this->presenter->typing($conversation, 'student');

        return response()->json(['ok' => true]);
    }
}
