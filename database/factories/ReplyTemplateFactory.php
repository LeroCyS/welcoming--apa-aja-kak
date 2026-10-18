<?php

namespace Database\Factories;

use App\Models\ReplyTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ReplyTemplate> */
class ReplyTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'body' => 'Hai {sapaan}, '.fake()->sentence(),
        ];
    }
}
