<?php

namespace App\Actions;

use App\Models\AuditLog;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ConversationMessenger;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Membuka identitas murid kepada guru yang menangani saat ini. Hanya bisa dipicu oleh murid sendiri. */
class RevealIdentityAction
{
    public function __construct(protected ConversationMessenger $messenger) {}

    public function execute(Conversation $c, User $student): void
    {
        abort_unless($c->isOwnedBy($student), 403);
        if (! $c->isAnonymousToCurrentCounselor()) {
            return;
        }
        // Identitas murid hanya berasal dari profil yang ia isi sendiri; tanpa nama tidak ada yang bisa dibuka.
        if (! $student->hasFilledIdentity()) {
            throw ValidationException::withMessages(['general' => 'Isi namamu dulu di Profil sebelum membuka identitas, ya.']);
        }

        DB::transaction(function () use ($c, $student) {
            $c->forceFill([
                'student_id' => $student->id,
                'identity_revealed_at' => now(),
                'revealed_to_counselor_id' => $c->counselor_id,
            ])->save();
            $this->messenger->system($c, 'Murid telah membuka identitasnya');
            AuditLog::record('identity_revealed', $c, ['counselor_id' => $c->counselor_id], $student->id);
        });

        Notify::counselor($c->fresh(), 'identity_revealed', $c->anonymous_alias.' telah membuka identitasnya');
        Realtime::conversation($c->uuid, 'ConversationUpdated', ['identity' => 'revealed']);
    }
}
