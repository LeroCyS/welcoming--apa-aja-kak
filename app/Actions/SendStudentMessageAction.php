<?php

namespace App\Actions;

use App\Models\Conversation;
use App\Models\Message;
use App\Services\CaseStatusService;
use App\Services\ConversationMessenger;
use App\Support\Notify;

class SendStudentMessageAction
{
    public function __construct(protected ConversationMessenger $messenger, protected CaseStatusService $status) {}

    public function text(Conversation $c, string $body): Message
    {
        return $this->execute($c, 'text', $body);
    }

    /** Satu jalur untuk semua pesan murid (teks, foto berketerangan, dst.): simpan, pindai kata kunci, lalu efek lanjutan. */
    public function execute(Conversation $c, string $type, ?string $body, ?array $meta = null): Message
    {
        $m = $this->messenger->add($c, 'student', $type, $body, $meta);
        if (filled($body)) {
            $this->messenger->scan($c, $body, $m);
        }
        $this->after($c, $m);

        return $m;
    }

    /** Dijalankan setelah setiap pesan murid (teks, foto, lagu, voice, berbagi mood). Setiap pesan dinotifikasikan ke guru. */
    public function after(Conversation $c, ?Message $m = null): void
    {
        if ($c->status === 'resolved') {
            $this->status->change($c, 'monitoring', null, 'Dibuka kembali: murid mengirim pesan baru');
        }
        $this->messenger->maybeAutoReply($c);
        $this->messenger->refreshPriority($c);

        $counselor = $c->counselor;
        Notify::user($counselor, 'new_message', 'Pesan baru dari '.$c->displayNameFor($counselor), route('counselor.conversations.show', $c), false, $m ? Notify::preview($m) : null, $m?->id);
    }
}
