<?php

namespace Database\Factories;

use App\Models\CounselorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CounselorProfile> */
class CounselorProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'counselor']),
            'nickname' => 'Bu '.fake()->firstNameFemale(),
            'gender' => 'female',
            'hobbies' => 'Membaca, berkebun',
            'favorite_food' => 'Nasi campur',
            'intro_message' => 'Hai, aku siap mendengarkan ceritamu tanpa menghakimi.',
            'offline_auto_reply' => null,
            'is_online' => false,
        ];
    }
}
