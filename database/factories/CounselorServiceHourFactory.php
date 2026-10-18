<?php

namespace Database\Factories;

use App\Models\CounselorServiceHour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CounselorServiceHour> */
class CounselorServiceHourFactory extends Factory
{
    public function definition(): array
    {
        return [
            'counselor_id' => User::factory()->counselor(),
            'day_of_week' => fake()->numberBetween(1, 5),
            'start_time' => '07:00:00',
            'end_time' => '15:00:00',
        ];
    }
}
