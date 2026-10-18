<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\FollowUp;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<FollowUp> */
class FollowUpFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'scheduled_at' => now()->addDays(7),
        ];
    }
}
