<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationFlag;
use App\Models\SensitiveKeyword;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ConversationFlag> */
class ConversationFlagFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sensitive_keyword_id' => SensitiveKeyword::factory(),
            'severity' => 1,
        ];
    }
}
