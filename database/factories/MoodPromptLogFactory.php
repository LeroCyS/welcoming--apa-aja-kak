<?php

namespace Database\Factories;

use App\Models\MoodPromptLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MoodPromptLog> */
class MoodPromptLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'shown_at' => now(),
        ];
    }
}
