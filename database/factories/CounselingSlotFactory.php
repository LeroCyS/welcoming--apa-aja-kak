<?php

namespace Database\Factories;

use App\Models\CounselingSlot;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CounselingSlot> */
class CounselingSlotFactory extends Factory
{
    public function definition(): array
    {
        return [
            'counselor_id' => User::factory()->counselor(),
            'start_at' => $s = now()->addDays(2)->setTime(9, 0),
            'end_at' => $s->copy()->addMinutes(45),
            'location' => 'Ruang BK',
            'status' => 'available',
        ];
    }
}
