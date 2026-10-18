<?php

namespace App\Actions;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\CaseStatusService;
use App\Services\ConversationMessenger;
use App\Services\StudentResolver;
use App\Support\Notify;

class SendCounselorMessageAction
{
    public function __construct(
        protected ConversationMessenger $messenger,
        protected CaseStatusService $status,
        protected StudentResolver $resolver,
    ) {}

    /** Setiap pesan guru dinotifikasikan ke murid. Judul tanpa kata "BK"; potongan pesan hanya tampil di dalam aplikasi. */
    public function execute(Conversation $c, User $counselor, string $type, ?string $body, ?array $meta = null, ?string $notifyText = null, string $kind = 'counselor_reply'): Message
    {
        $m = $this->messenger->add($c, 'counselor', $type, $body, $meta, $counselor->id);

        if ($c->status === 'new') {
            $this->status->change($c, 'monitoring', $counselor->id, 'Otomatis saat guru membalas');
        } else {
            $this->messenger->refreshPriority($c);
        }

        Notify::user($this->resolver->resolve($c), $kind, $notifyText ?? $counselor->displayName().' membalas ceritamu', route('student.conversations.show', $c), false, Notify::preview($m), $m->id);

        return $m;
    }
}
