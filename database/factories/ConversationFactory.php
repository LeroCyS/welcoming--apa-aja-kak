<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\School;
use App\Models\User;
use App\Support\IdentityHasher;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Conversation> */
class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'school_id' => fn () => School::query()->value('id') ?? School::factory(),
            'counselor_id' => User::factory()->counselor(),
            'student_ref' => fn (array $a) => '0',
            'student_hash' => fn (array $a) => IdentityHasher::hash(0),
            'grade_level' => 11,
            'is_anonymous' => true,
            'anonymous_alias' => 'Kucing Oranye #'.fake()->numberBetween(100, 999),
            'anonymous_avatar' => 'kucing--oranye',
            'urgency' => 'normal',
            'status' => 'new',
            'last_message_at' => now(),
        ];
    }

    public function forStudent(User $student, bool $anonymous = true): static
    {
        return $this->state(fn () => [
            'student_ref' => (string) $student->id,
            'student_hash' => IdentityHasher::hash($student->id),
            'student_id' => $anonymous ? null : $student->id,
            'is_anonymous' => $anonymous,
            'grade_level' => $student->studentProfile?->grade_level ?? 11,
        ]);
    }

    public function revealedTo(User $student, User $counselor): static
    {
        return $this->forStudent($student)->state(fn () => [
            'counselor_id' => $counselor->id,
            'student_id' => $student->id,
            'revealed_to_counselor_id' => $counselor->id,
            'identity_revealed_at' => now(),
        ]);
    }
}
