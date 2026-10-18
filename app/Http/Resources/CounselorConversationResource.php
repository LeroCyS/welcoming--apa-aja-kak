<?php

namespace App\Http\Resources;

use App\Models\Conversation;
use App\Services\PriorityCalculator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * View-model percakapan untuk Guru BK.
 * TIDAK PERNAH memuat student_ref, student_hash, student_id, maupun grade_level.
 * Nama & kelas hanya muncul bila identitas memang terlihat oleh guru ini.
 *
 * @mixin Conversation
 */
class CounselorConversationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Conversation $c */
        $c = $this->resource;
        $viewer = $request->user();
        $visible = $c->identityVisibleTo($viewer);
        $student = $visible ? $c->revealedStudent : null;
        $breakdown = app(PriorityCalculator::class)->breakdown($c);
        $last = $c->relationLoaded('latestMessage') ? $c->latestMessage : null;

        return [
            'uuid' => $c->uuid,
            'url' => route('counselor.conversations.show', $c),
            'display_name' => $student?->name ?? $c->anonymous_alias,
            'alias' => $c->anonymous_alias,
            'avatar' => $c->anonymous_avatar,
            'identified' => (bool) $student,
            'class_name' => $student?->studentProfile?->class_name,
            'revealed_at' => $visible && $c->is_anonymous ? $c->identity_revealed_at?->toIso8601String() : null,
            'topics' => $c->topics->map(fn ($t) => ['slug' => $t->slug, 'name' => $t->name])->values(),
            'urgency' => $c->urgencyKey(),
            'status' => $c->status,
            'status_label' => $c->statusLabel(),
            'priority' => $c->priority_score,
            'reasons' => $breakdown['reasons'],
            'flagged' => (int) ($c->unreviewed_flags_count ?? $c->unreviewedFlags()->count()) > 0,
            'is_emergency' => $c->is_emergency,
            // Konteks anti-iseng tanpa identitas: akun baru bergabung, murid sudah pindah, atau sudah ditandai iseng.
            'new_member' => $c->from_new_member,
            'archived' => $c->isArchived(),
            'prank' => $c->prank_reported_at !== null,
            'preview' => $last ? ($last->sender_type === 'counselor' ? 'Kamu: ' : '').$last->preview(110) : '',
            'preview_mine' => $last?->sender_type === 'counselor',
            'preview_icon' => $last?->previewIcon(),
            'last_message_at' => $c->last_message_at?->toIso8601String(),
            'last_human' => $c->last_message_at?->diffForHumans(short: true),
            'unread' => (int) ($c->unread_count ?? 0),
        ];
    }
}
