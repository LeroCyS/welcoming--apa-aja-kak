<?php

namespace Database\Factories;

use App\Models\MoodEntry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MoodEntry> */
class MoodEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'entry_date' => now()->toDateString(),
            'mood' => fake()->randomElement(MoodEntry::MOODS),
        ];
    }
}
