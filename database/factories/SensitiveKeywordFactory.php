<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SensitiveKeyword;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SensitiveKeyword> */
class SensitiveKeywordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => fn () => School::query()->value('id') ?? School::factory(),
            'phrase' => fake()->unique()->words(2, true),
            'severity' => 1,
            'category' => 'distress',
            'is_active' => true,
        ];
    }
}
