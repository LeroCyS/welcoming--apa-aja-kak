<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageHide;
use App\Models\User;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Edit & hapus pesan seperti WhatsApp, untuk murid maupun Guru BK.
 * Pesan murid berkata kunci kritis tetap terbaca oleh Guru BK (isi asli disimpan), agar tanda bahaya tidak hilang.
 */
class MessageEditor
{
    public function __construct(protected ConversationMessenger $messenger) {}

    /** Ubah teks pesan sendiri (maks. 15 menit). */
    public function edit(Message $m, User $user, string $body): Message
    {
        if (! $m->canBeEditedBy($user)) {
            throw ValidationException::withMessages(['body' => 'Pesan ini sudah tidak bisa diedit (maks. '.Message::EDIT_MINUTES.' menit setelah dikirim).']);
        }
        if ($body === $m->body) {
            return $m;
        }

        $previousPreview = Notify::preview($m);
        $m->forceFill([
            'original_body' => $m->original_body ?? ($m->isCritical() ? $m->body : null),
            'body' => $body,
            'edited_at' => now(),
        ])->save();
        Notify::syncMessagePreview($m, $previousPreview);

        $c = $m->conversation;
        if ($m->isFromStudent()) {
            $this->messenger->scan($c, $body, $m);
            $this->messenger->refreshPriority($c);
        }
        $this->ping($c);

        return $m;
    }

    /** Hapus untuk semua orang (maks. 2 hari 12 jam, seperti WhatsApp). */
    public function retract(Message $m, User $user): void
    {
        if (! $m->canBeRetractedBy($user)) {
            throw ValidationException::withMessages(['message' => 'Pesan ini sudah tidak bisa dihapus untuk semua orang (maks. '.Message::RETRACT_LABEL.' setelah dikirim).']);
        }

        $this->retractQuietly($m);
        $this->ping($m->conversation);
    }

    /** Hapus untuk saya: hanya disembunyikan dari pengguna ini. */
    public function hideFor(Message $m, User $user): void
    {
        MessageHide::firstOrCreate(['message_id' => $m->id, 'user_id' => $user->id]);
    }

    /** Tandai terhapus. Pesan kritis tetap disimpan untuk Guru BK; selain itu isi & medianya dibuang. */
    protected function retractQuietly(Message $m): void
    {
        // Pesan kritis tetap terbaca Guru BK, termasuk di notifikasinya.
        if ($m->isCritical()) {
            $m->forceFill(['retracted_at' => now()])->save();

            return;
        }
        $previousPreview = Notify::preview($m);

        if ($path = $m->meta['image']['path'] ?? null) {
            Storage::disk('local')->delete($path);
        }
        if ($vn = $m->voiceNote) {
            if (! $vn->audio_deleted_at) {
                Storage::disk('local')->delete($vn->file_path);
            }
            $vn->forceFill(['audio_deleted_at' => now(), 'transcript' => null])->save();
        }

        $m->forceFill(['body' => null, 'original_body' => null, 'meta' => null, 'retracted_at' => now()])->save();
        Notify::syncMessagePreview($m, $previousPreview);
    }

    /** Kabari kedua pihak agar ruang chat dimuat ulang. */
    protected function ping(Conversation $c): void
    {
        Realtime::conversation($c->uuid, 'ConversationUpdated');
        Realtime::counselor($c->counselor_id, 'InboxUpdated', ['conversation' => $c->uuid]);
    }
}
