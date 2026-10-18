<?php

namespace App\Services;

use App\Http\Resources\CounselorConversationResource;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class InboxService
{
    /** Percakapan milik guru ini saja, urut: non-resolved → prioritas DESC → menunggu paling lama. */
    public function list(User $counselor, array $f = []): Collection
    {
        $q = Conversation::query()->where('counselor_id', $counselor->id)
            ->with(['topics', 'latestMessage', 'revealedStudent.studentProfile'])
            ->withCount([
                'unreviewedFlags',
                'messages as unread_count' => fn ($m) => $m->where('sender_type', 'student')->whereNull('read_at'),
            ])
            ->when($f['topic'] ?? null, fn ($w, $slug) => $w->whereHas('topics', fn ($t) => $t->where('slug', $slug)))
            ->when($f['urgency'] ?? null, fn ($w, $u) => $u === Conversation::UNASSESSED ? $w->whereNull('urgency') : $w->where('urgency', $u))
            ->when($f['status'] ?? null, fn ($w, $s) => $w->where('status', $s))
            ->orderByRaw("status = 'resolved'")
            ->orderByDesc('priority_score')
            ->orderByRaw('last_student_message_at IS NULL')
            ->orderBy('last_student_message_at');

        $items = $q->get();

        // Filter identitas & pencarian dilakukan setelah query agar hanya identitas yang memang terlihat yang bisa dicari.
        $identity = $f['identity'] ?? null;
        $search = mb_strtolower(trim((string) ($f['q'] ?? '')));

        return $items->filter(function (Conversation $c) use ($counselor, $identity, $search) {
            $visible = $c->identityVisibleTo($counselor) && $c->revealedStudent;
            if ($identity === 'anonim' && $visible) {
                return false;
            }
            if ($identity === 'teridentifikasi' && ! $visible) {
                return false;
            }
            if ($search !== '') {
                $hay = mb_strtolower($c->anonymous_alias.' '.($visible ? $c->revealedStudent->name : '').' '.$c->topics->pluck('name')->implode(' ').' '.$c->statusLabel());

                return str_contains($hay, $search);
            }

            return true;
        })->values();
    }

    public function present(Collection $items, Request $request): array
    {
        return CounselorConversationResource::collection($items)->resolve($request);
    }
}
