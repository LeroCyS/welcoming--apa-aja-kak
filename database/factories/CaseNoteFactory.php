<?php

namespace Database\Factories;

use App\Models\CaseNote;
use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CaseNote> */
class CaseNoteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'counselor_id' => fn (array $a) => Conversation::find($a['conversation_id'])->counselor_id,
            'body' => fake()->paragraph(),
        ];
    }
}
