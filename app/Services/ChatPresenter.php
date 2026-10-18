<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Conversation;
use App\Models\ConversationFlag;
use App\Models\CounselingSession;
use App\Models\FollowUp;
use App\Models\IdentityRequest;
use App\Models\Message;
use App\Models\MoodEntry;
use App\Models\School;
use App\Models\SessionInvitation;
use App\Models\User;
use App\Models\VoiceNote;
use App\Support\Notify;
use App\Support\Realtime;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Merender pesan sebagai HTML sesuai peran penonton (murid/guru).
 * Dipakai untuk render awal, polling (?after=id), dan setelah kirim pesan.
 */
class ChatPresenter
{
    /** @return list<array{id: int, html: string}> */
    public function render(Conversation $c, Collection $messages, User $viewer, ?Message $previous = null): array
    {
        if ($messages->isEmpty()) {
            return [];
        }
        $role = $viewer->isCounselor() ? 'counselor' : 'student';
        $ctx = $this->context($c, $messages, $viewer);
        $tz = School::current()->timezone;
        $prevDate = $previous?->created_at->timezone($tz)->toDateString();

        $out = [];
        foreach ($messages as $m) {
            $m->setRelation('conversation', $c);
            $date = $m->created_at->timezone($tz);
            $separator = $date->toDateString() !== $prevDate ? $this->dateLabel($date) : null;
            $prevDate = $date->toDateString();
            $out[] = [
                'id' => $m->id,
                'html' => view('chat.message', ['m' => $m, 'role' => $role, 'c' => $c, 'ctx' => $ctx, 'separator' => $separator, 'tz' => $tz])->render(),
            ];
        }

        return $out;
    }

    protected function dateLabel($date): string
    {
        if ($date->isToday()) {
            return 'Hari ini';
        }
        if ($date->isYesterday()) {
            return 'Kemarin';
        }

        return $date->translatedFormat('l, j M Y');
    }

    protected function context(Conversation $c, Collection $messages, User $viewer): array
    {
        $meta = fn (string $key) => $messages->pluck('meta.'.$key)->filter()->unique()->values();

        return [
            'viewer' => $viewer,
            'counselorName' => $c->counselor?->displayName() ?? 'Guru BK',
            'anonToCounselor' => $c->isAnonymousToCurrentCounselor(),
            'invitations' => SessionInvitation::with(['slots', 'session.slot', 'counselor.counselorProfile'])->whereIn('id', $meta('invitation_id'))->get()->keyBy('id'),
            'identityRequests' => IdentityRequest::with('counselor.counselorProfile')->whereIn('id', $meta('identity_request_id'))->get()->keyBy('id'),
            'followUps' => FollowUp::whereIn('id', $meta('follow_up_id'))->get()->keyBy('id'),
            'articles' => Article::with('category')->whereIn('id', $meta('article_id'))->get()->keyBy('id'),
            'moods' => MoodEntry::whereIn('id', $meta('mood_entry_id'))->get()->keyBy('id'),
            'flags' => $viewer->isCounselor()
                ? ConversationFlag::with('keyword')->whereIn('message_id', $messages->pluck('id'))->get()->groupBy('message_id')
                : collect(),
            'contacts' => $messages->contains('type', 'emergency_info') ? School::current()->emergencyContacts()->where('is_active', true)->get() : collect(),
        ];
    }

    /** Versi state percakapan: berubah bila kartu interaktif / status / identitas berubah. */
    public function stateVersion(Conversation $c): string
    {
        $c->refresh();
        $parts = [
            $c->status, $c->counselor_id, $c->revealed_to_counselor_id, $c->identity_revealed_at?->timestamp,
            $c->invitations()->max('updated_at'), $c->identityRequests()->max('updated_at'), $c->followUps()->max('updated_at'),
            VoiceNote::where('conversation_id', $c->id)->max('updated_at'),
            CounselingSession::where('conversation_id', $c->id)->max('updated_at'),
            // Pesan diedit / dihapus untuk semua orang.
            $c->messages()->reorder()->max('edited_at'), $c->messages()->reorder()->max('retracted_at'),
        ];

        return substr(sha1(implode('|', array_map('strval', $parts))), 0, 12);
    }

    /** Tandai pesan dari pihak lawan sebagai dibaca (sekaligus notifikasi untuk percakapan ini). */
    public function markRead(Conversation $c, User $viewer): int
    {
        $query = $c->messages()->reorder()->whereNull('read_at');
        $viewer->isCounselor() ? $query->where('sender_type', 'student') : $query->where('sender_type', '!=', 'student');
        $n = $query->update(['read_at' => now(), 'delivered_at' => DB::raw('COALESCE(delivered_at, NOW())')]);
        if ($n > 0) {
            Notify::markConversationRead($viewer, $c);
            Realtime::conversation($c->uuid, 'MessageRead', ['by' => $viewer->isCounselor() ? 'counselor' : 'student']);
        }

        return $n;
    }

    /**
     * Pesan untuk user ini sudah sampai ke perangkatnya: dipanggil dari polling yang berjalan di setiap halaman
     * (lonceng notifikasi, heartbeat guru, status guru untuk murid). Pengirim lalu melihat centang dua abu-abu.
     */
    public function markDelivered(User $recipient): int
    {
        if ($recipient->isCounselor()) {
            $conversations = Conversation::where('counselor_id', $recipient->id);
            $from = fn ($q) => $q->where('sender_type', 'student');
        } elseif ($recipient->isStudent()) {
            $conversations = Conversation::forStudent($recipient);
            $from = fn ($q) => $q->where('sender_type', 'counselor');
        } else {
            return 0;
        }

        $pending = Message::whereNull('delivered_at')->whereIn('conversation_id', $conversations->select('id'))->where($from);
        $uuids = Conversation::whereIn('id', (clone $pending)->select('conversation_id'))->pluck('uuid');
        if ($uuids->isEmpty()) {
            return 0;
        }
        $n = $pending->update(['delivered_at' => now()]);
        $uuids->each(fn (string $uuid) => Realtime::conversation($uuid, 'MessageDelivered'));

        return $n;
    }

    /**
     * Data refresh chat (render awal, polling ?after=id / ?full=1, dan setelah kirim pesan) untuk murid maupun guru.
     *
     * @return array<string, mixed>
     */
    public function payload(Request $request, Conversation $c): array
    {
        $viewer = $request->user();
        $full = $request->boolean('full');
        $after = $full ? 0 : $request->integer('after');
        $this->markRead($c, $viewer);
        $messages = $c->messages()->with('voiceNote')->visibleTo($viewer)->where('id', '>', $after)->get();
        $previous = $after ? $c->messages()->reorder()->visibleTo($viewer)->where('id', '<=', $after)->latest('id')->first() : null;
        $mine = $viewer->isCounselor() ? 'counselor' : 'student';

        $payload = [
            'full' => $full,
            'messages' => $this->render($c, $messages, $viewer, $previous),
            'last_id' => (int) ($messages->max('id') ?? $after),
            'version' => $this->stateVersion($c),
            'read_ids' => $c->messages()->reorder()->where('sender_type', $mine)->whereNotNull('read_at')->where('id', '>', max(0, $after - 50))->pluck('id'),
            'delivered_ids' => $c->messages()->reorder()->where('sender_type', $mine)->whereNotNull('delivered_at')->whereNull('read_at')->where('id', '>', max(0, $after - 50))->pluck('id'),
            'typing' => $this->isTyping($c, $mine === 'student' ? 'counselor' : 'student'),
        ];
        if ($viewer->isStudent()) {
            $payload['status'] = $c->studentStatusLabel();
            $payload['anonymous'] = $c->isAnonymousToCurrentCounselor();
        }

        return $payload;
    }

    /** Catat bahwa salah satu pihak sedang mengetik (berlaku beberapa detik) dan kabari lawan chat. */
    public function typing(Conversation $c, string $role): void
    {
        Cache::put($this->typingKey($c, $role), true, now()->addSeconds(6));
        Realtime::conversation($c->uuid, 'Typing', ['role' => $role]);
    }

    public function isTyping(Conversation $c, string $role): bool
    {
        return Cache::has($this->typingKey($c, $role));
    }

    /** Pesan baru dari pengetik berarti ia sudah selesai mengetik. */
    public function stopTyping(Conversation $c, string $role): void
    {
        Cache::forget($this->typingKey($c, $role));
    }

    protected function typingKey(Conversation $c, string $role): string
    {
        return 'typing:'.$c->id.':'.$role;
    }
}
