<?php

namespace App\Actions;

use App\Models\CounselingSession;
use App\Models\CounselingSlot;
use App\Models\SessionInvitation;
use App\Models\User;
use App\Services\ConversationMessenger;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AcceptInvitationAction
{
    public function __construct(protected RevealIdentityAction $reveal, protected ConversationMessenger $messenger) {}

    public function execute(SessionInvitation $inv, User $student, int $slotId, bool $confirmReveal): CounselingSession
    {
        $c = $inv->conversation;
        abort_unless($c->isOwnedBy($student), 403);
        if (! $inv->isOpen()) {
            throw ValidationException::withMessages(['slot_id' => 'Undangan ini sudah tidak berlaku.']);
        }
        if (! $inv->slots()->whereKey($slotId)->exists()) {
            throw ValidationException::withMessages(['slot_id' => 'Pilih salah satu jadwal yang ditawarkan.']);
        }

        // Sesi tatap muka selalu teridentifikasi: buka identitas bila masih anonim terhadap guru ini.
        if ($c->isAnonymousToCurrentCounselor()) {
            if (! $confirmReveal) {
                throw ValidationException::withMessages(['slot_id' => 'Untuk bertemu langsung, kamu perlu menyetujui membuka identitas.']);
            }
            $this->reveal->execute($c, $student);
        }

        $session = DB::transaction(function () use ($inv, $student, $slotId, $c) {
            /** @var CounselingSlot|null $slot */
            $slot = CounselingSlot::whereKey($slotId)->lockForUpdate()->first();
            if (! $slot || $slot->status !== 'available' || $slot->start_at->isPast()) {
                throw ValidationException::withMessages(['slot_id' => 'Yah, jadwal itu baru saja dipilih orang lain. Coba pilih waktu lain, ya.']);
            }
            $fresh = SessionInvitation::whereKey($inv->id)->lockForUpdate()->first();
            if ($fresh->status !== 'pending') {
                throw ValidationException::withMessages(['slot_id' => 'Undangan ini sudah dijawab.']);
            }

            $slot->update(['status' => 'booked']);
            $fresh->update(['status' => 'accepted', 'responded_at' => now()]);

            return CounselingSession::create([
                'uuid' => (string) Str::uuid(),
                'conversation_id' => $c->id,
                'session_invitation_id' => $inv->id,
                'counseling_slot_id' => $slot->id,
                'counselor_id' => $inv->counselor_id,
                'student_id' => $student->id,
                'status' => 'scheduled',
            ]);
        });

        $slot = $session->slot;
        $this->messenger->system($c, 'Jadwal ngobrol langsung sudah dipilih: '.$slot->label().'. Detailnya ada di Jadwal Saya.');
        Notify::counselor($c, 'invitation_accepted', 'Undangan sesi diterima: '.$slot->label(), false);
        Realtime::conversation($c->uuid, 'ConversationUpdated', ['invitation' => $inv->id]);

        return $session;
    }
}
