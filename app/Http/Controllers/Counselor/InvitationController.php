<?php

namespace App\Http\Controllers\Counselor;

use App\Actions\SendCounselorMessageAction;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\CounselingSlot;
use App\Models\SessionInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InvitationController extends Controller
{
    public function store(Request $request, Conversation $conversation, SendCounselorMessageAction $send): RedirectResponse
    {
        $this->authorize('reply', $conversation);
        $user = $request->user();
        $data = $request->validate([
            'slot_ids' => ['required', 'array', 'min:1', 'max:5'],
            'slot_ids.*' => ['integer', 'distinct'],
            'message' => ['nullable', 'string', 'max:255'],
        ], ['slot_ids.required' => 'Pilih minimal satu slot.', 'slot_ids.max' => 'Maksimal 5 slot per undangan.']);

        $slots = CounselingSlot::whereIn('id', $data['slot_ids'])->where('counselor_id', $user->id)
            ->where('status', 'available')->where('start_at', '>=', now()->addHours(2))->get();
        if ($slots->count() !== count($data['slot_ids'])) {
            throw ValidationException::withMessages(['slot_ids' => 'Ada slot yang sudah tidak tersedia atau kurang dari 2 jam lagi.']);
        }

        $inv = SessionInvitation::create([
            'conversation_id' => $conversation->id,
            'counselor_id' => $user->id,
            'message' => $data['message'] ?? null,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);
        $inv->slots()->attach($slots->pluck('id'));
        $send->execute($conversation, $user, 'session_invite', $inv->message, ['invitation_id' => $inv->id], 'Ada ajakan ngobrol langsung untukmu', 'session_invite');

        return redirect()->route('counselor.conversations.show', $conversation)->with('status', 'Undangan terkirim dengan '.$slots->count().' pilihan waktu.');
    }
}
