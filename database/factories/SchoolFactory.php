<?php

namespace Database\Factories;

use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<School> */
class SchoolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'SMA '.fake()->lastName(),
            'level' => 'sma',
            'timezone' => 'Asia/Makassar',
            'default_service_start' => '07:00:00',
            'default_service_end' => '15:00:00',
        ];
    }
}
