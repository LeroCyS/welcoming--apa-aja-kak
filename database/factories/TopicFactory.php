<?php

namespace Database\Factories;

use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Topic> */
class TopicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'icon' => 'chat',
            'color' => 'terracotta',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
