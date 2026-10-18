<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolTransferRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SchoolTransferRequest>
 */
class SchoolTransferRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'student_id' => User::factory()->student(),
            'school_id' => fn () => School::query()->value('id') ?? School::factory(),
            'reason' => fake()->randomElement(array_keys(SchoolTransferRequest::REASONS)),
            'status' => 'pending',
        ];
    }
}
