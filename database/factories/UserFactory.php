<?php

namespace Database\Factories;

use App\Http\Controllers\Auth\GoogleController;
use App\Models\CounselorProfile;
use App\Models\CounselorServiceHour;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'school_id' => fn () => School::query()->value('id') ?? School::factory(),
            'role' => 'student',
            'name' => fake()->name(),
            'email' => null,
            'identifier' => (string) fake()->unique()->numerify('00########'),
            'password' => static::$password ??= Hash::make('password'),
            'must_change_password' => false,
            'is_active' => true,
        ];
    }

    public function student(?int $grade = null, ?string $class = null): static
    {
        return $this->state(fn () => ['role' => 'student', 'identifier' => null, 'email' => fake()->unique()->safeEmail()])->afterCreating(function (User $u) use ($grade, $class) {
            $g = $grade ?? fake()->numberBetween(10, 12);
            StudentProfile::factory()->create([
                'user_id' => $u->id,
                'grade_level' => $g,
                'class_name' => $class ?? (['10' => 'X', '11' => 'XI', '12' => 'XII'][$g] ?? $g).' '.fake()->randomElement(['IPA', 'IPS']).' '.fake()->numberBetween(1, 3),
            ]);
        });
    }

    public function counselor(): static
    {
        return $this->state(['role' => 'counselor', 'identifier' => 'guru'.fake()->unique()->numberBetween(100, 9999)])
            ->afterCreating(function (User $u) {
                CounselorProfile::factory()->create(['user_id' => $u->id, 'nickname' => 'Bu '.explode(' ', $u->name)[0]]);
                foreach (range(1, 5) as $d) {
                    CounselorServiceHour::create(['counselor_id' => $u->id, 'day_of_week' => $d, 'start_time' => '07:00:00', 'end_time' => '15:00:00']);
                }
            });
    }

    public function admin(): static
    {
        return $this->state(['role' => 'admin', 'identifier' => 'admin'.fake()->unique()->numberBetween(100, 9999)]);
    }

    /** Murid yang mendaftar lewat Google dan belum membuat password. */
    public function viaGoogle(): static
    {
        // Akun Google hanya menyimpan hash ID Google, tanpa email dan password.
        return $this->state(fn () => ['google_id' => GoogleController::googleHash(fake()->unique()->numerify('1#################')), 'email' => null, 'password' => null]);
    }

    public function mustChangePassword(): static
    {
        return $this->state(['must_change_password' => true]);
    }
}
