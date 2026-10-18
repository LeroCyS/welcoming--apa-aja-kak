<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\IdentityRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<IdentityRequest> */
class IdentityRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'counselor_id' => fn (array $a) => Conversation::find($a['conversation_id'])->counselor_id,
            'status' => 'pending',
        ];
    }
}
