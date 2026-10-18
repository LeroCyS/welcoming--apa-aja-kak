<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class School extends Model
{
    use HasFactory;

    /** Jenjang beserta rentang tingkat kelasnya. */
    public const LEVELS = [
        'sd' => ['label' => 'SD', 'grades' => [1, 6]],
        'smp' => ['label' => 'SMP', 'grades' => [7, 9]],
        'sma' => ['label' => 'SMA', 'grades' => [10, 12]],
        'smk' => ['label' => 'SMK', 'grades' => [10, 12]],
    ];

    protected $fillable = ['name', 'level', 'logo_path', 'address', 'timezone', 'default_service_start', 'default_service_end'];

    /**
     * Sekolah milik user yang sedang login. Untuk tamu (landing, login) dan proses tanpa login
     * (seeder, command), jatuh ke sekolah pertama sebagai default zona waktu & tampilan.
     */
    public static function current(): self
    {
        $user = Auth::user();
        if ($user instanceof User && $user->school_id !== null) {
            return $user->school;
        }

        return once(fn () => static::query()->orderBy('id')->firstOrFail());
    }

    public function levelLabel(): string
    {
        return self::LEVELS[$this->level]['label'] ?? Str::upper($this->level);
    }

    /** @return array{0: int, 1: int} */
    public function gradeRange(): array
    {
        return self::LEVELS[$this->level]['grades'] ?? [1, 12];
    }

    public function isBanned(string $studentHash): bool
    {
        return $this->bans()->where('student_hash', $studentHash)->exists();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function bans(): HasMany
    {
        return $this->hasMany(SchoolBan::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class)->orderBy('sort_order');
    }
}
