<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\CounselingSession;
use App\Models\CounselingSlot;
use App\Models\SessionInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<CounselingSession> */
class CounselingSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'conversation_id' => Conversation::factory(),
            'session_invitation_id' => SessionInvitation::factory(),
            'counseling_slot_id' => CounselingSlot::factory(),
            'counselor_id' => User::factory()->counselor(),
            'student_id' => User::factory()->student(),
            'status' => 'scheduled',
        ];
    }
}
