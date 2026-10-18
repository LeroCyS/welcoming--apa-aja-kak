<?php

namespace Database\Factories;

use App\Models\CaseTransfer;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CaseTransfer> */
class CaseTransferFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'from_counselor_id' => User::factory()->counselor(),
            'to_counselor_id' => User::factory()->counselor(),
            'handover_note' => fake()->sentence(),
        ];
    }
}
