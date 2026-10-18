<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StudentProfile> */
class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'class_name' => 'XI IPA 2',
            'grade_level' => 11,
            'onboarded_at' => now(),
            'tour_completed_at' => now(),
        ];
    }

    /** Murid yang baru mendaftar dan belum menyelesaikan onboarding. */
    public function newlyRegistered(): static
    {
        return $this->state(['class_name' => null, 'grade_level' => null, 'onboarded_at' => null, 'tour_completed_at' => null]);
    }
}
