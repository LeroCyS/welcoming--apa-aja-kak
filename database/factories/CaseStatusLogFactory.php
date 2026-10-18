<?php

namespace Database\Factories;

use App\Models\CaseStatusLog;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CaseStatusLog> */
class CaseStatusLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'from_status' => 'new',
            'to_status' => 'monitoring',
            'created_at' => now(),
        ];
    }
}
