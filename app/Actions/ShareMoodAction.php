<?php

namespace App\Actions;

use App\Models\Conversation;
use App\Models\MoodEntry;
use App\Services\ConversationMessenger;

class ShareMoodAction
{
    public function __construct(protected ConversationMessenger $messenger, protected SendStudentMessageAction $studentMessage) {}

    public function execute(MoodEntry $entry, Conversation $c): void
    {
        $m = $this->messenger->add($c, 'student', 'mood_share', $entry->note, array_filter(['mood_entry_id' => $entry->id, 'mood' => $entry->mood, 'date' => $entry->entry_date->toDateString(), 'song' => $entry->song]));
        $entry->forceFill(['shared_at' => now(), 'shared_conversation_id' => $c->id])->save();
        if (filled($entry->note)) {
            $this->messenger->scan($c, $entry->note, $m);
        }
        $this->studentMessage->after($c, $m);
    }
}
