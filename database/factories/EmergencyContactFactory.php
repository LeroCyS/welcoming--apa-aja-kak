<?php

namespace Database\Factories;

use App\Models\EmergencyContact;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<EmergencyContact> */
class EmergencyContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => fn () => School::query()->value('id') ?? School::factory(),
            'name' => fake()->company(),
            'description' => fake()->sentence(),
            'phone' => '[Nomor hotline]',
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
