<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\ConversationFlag;
use App\Models\CounselorProfile;
use App\Models\Message;
use App\Support\Notify;
use App\Support\Realtime;

/** Membuat pesan & efek sampingnya (timestamp, broadcast, deteksi kata kunci, auto-reply, info darurat). */
class ConversationMessenger
{
    public function __construct(
        protected KeywordScanner $scanner,
        protected PriorityCalculator $priority,
        protected AvailabilityService $availability,
        protected ChatPresenter $presenter,
    ) {}

    public function add(Conversation $c, string $senderType, string $type, ?string $body = null, ?array $meta = null, ?int $senderId = null): Message
    {
        $m = $c->messages()->create([
            'sender_type' => $senderType,
            'sender_id' => $senderType === 'counselor' ? $senderId : null,
            'type' => $type,
            'body' => $body,
            'meta' => $meta,
        ]);

        $now = $m->created_at;
        $updates = ['last_message_at' => $now];
        if ($senderType === 'student') {
            $updates['last_student_message_at'] = $now;
        } elseif ($senderType === 'counselor') {
            $updates['last_counselor_message_at'] = $now;
            if (! $c->first_response_at) {
                $updates['first_response_at'] = $now;
            }
        }
        $c->forceFill($updates)->save();
        if ($senderType !== 'system') {
            $this->presenter->stopTyping($c, $senderType);
        }

        $this->ping($c, $m);

        return $m;
    }

    public function system(Conversation $c, string $body, string $type = 'system', ?array $meta = null): Message
    {
        return $this->add($c, 'system', $type, $body, $meta);
    }

    /** Ping realtime ke percakapan dan inbox guru (tanpa data identitas). */
    public function ping(Conversation $c, ?Message $m = null): void
    {
        Realtime::conversation($c->uuid, 'MessageSent', ['message_id' => $m?->id]);
        Realtime::counselor($c->counselor_id, 'InboxUpdated', ['conversation' => $c->uuid]);
    }

    /** Kartu kontak darurat; maksimal sekali per 6 jam per percakapan kecuali $force. */
    public function sendEmergencyInfo(Conversation $c, bool $force = false): ?Message
    {
        if (! $force) {
            $recent = $c->messages()->reorder()->where('type', 'emergency_info')
                ->where('created_at', '>=', now()->subHours(config('welcoming.emergency_info_cooldown_hours')))->exists();
            if ($recent) {
                return null;
            }
        }

        return $this->system($c, 'Kamu tidak sendirian. Kalau kamu sedang dalam bahaya atau tidak aman, segera hubungi salah satu kontak di bawah ini. Guru BK juga sudah diberi tahu.', 'emergency_info');
    }

    /** Pindai teks murid → flag; severity 3 memicu info darurat + notifikasi "Kasus kritis". */
    public function scan(Conversation $c, string $text, ?Message $source = null): int
    {
        $maxSeverity = 0;
        foreach ($this->scanner->scan($text, $c->school_id) as $kw) {
            $exists = $source && ConversationFlag::where('message_id', $source->id)->where('sensitive_keyword_id', $kw->id)->exists();
            if ($exists) {
                continue;
            }
            ConversationFlag::create([
                'conversation_id' => $c->id,
                'message_id' => $source?->id,
                'sensitive_keyword_id' => $kw->id,
                'severity' => $kw->severity,
            ]);
            $maxSeverity = max($maxSeverity, $kw->severity);
        }

        if ($maxSeverity >= 3) {
            $this->sendEmergencyInfo($c);
            Notify::counselor($c, 'critical', 'Kasus kritis: '.$c->anonymous_alias.', terdeteksi kata sensitif tingkat kritis', true);
        }

        return $maxSeverity;
    }

    /**
     * Auto-reply bila guru offline ATAU pesan masuk di luar jam layanannya. Terkirim sekali per percakapan
     * sampai guru membalas; setelah guru membalas, auto-reply bisa terkirim lagi untuk pesan berikutnya.
     */
    public function maybeAutoReply(Conversation $c): ?Message
    {
        $counselor = $c->counselor()->with(['counselorProfile', 'serviceHours'])->first();
        if (! $counselor || ($this->availability->isOnline($counselor) && $this->availability->inServiceHours($counselor))) {
            return null;
        }
        if ($c->last_auto_reply_at && (! $c->last_counselor_message_at || $c->last_auto_reply_at->gt($c->last_counselor_message_at))) {
            return null;
        }

        $body = strtr($counselor->counselorProfile?->autoReplyTemplate() ?? CounselorProfile::DEFAULT_AUTO_REPLY, [
            '{guru}' => $counselor->displayName(),
            '{jam_layanan}' => $this->availability->nextServiceLabel($counselor) ?? 'secepatnya',
        ]);
        $m = $this->system($c, $body, 'auto_reply', ['counselor' => $counselor->displayName()]);
        $c->forceFill(['last_auto_reply_at' => now()])->save();

        return $m;
    }

    public function refreshPriority(Conversation $c): int
    {
        return $this->priority->refresh($c);
    }
}
