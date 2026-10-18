<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\StoryPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StoryPost> */
class StoryPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => fn () => School::query()->value('id') ?? School::factory(),
            'author_hash' => hash('sha256', fake()->uuid()),
            'display_label' => 'Seorang murid',
            'body' => fake()->paragraph(3),
            'status' => 'approved',
            'moderated_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending', 'moderated_at' => null]);
    }
}
