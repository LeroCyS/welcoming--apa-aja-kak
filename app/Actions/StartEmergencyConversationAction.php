<?php

namespace App\Actions;

use App\Models\Conversation;
use App\Models\Topic;
use App\Models\User;

class StartEmergencyConversationAction
{
    public function __construct(protected StartConversationAction $start) {}

    public function execute(User $student): Conversation
    {
        return $this->start->execute($student, [
            'counselor_id' => null,
            'topics' => [Topic::where('slug', 'belum-tahu')->value('id')],
            'urgency' => 'urgent',
            'is_anonymous' => true,
            'message' => null,
            'is_emergency' => true,
            'system_first' => 'Murid menekan tombol Bantuan Darurat',
        ]);
    }
}
