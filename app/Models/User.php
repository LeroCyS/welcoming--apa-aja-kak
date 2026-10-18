<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use NotificationChannels\WebPush\HasPushSubscriptions;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPushSubscriptions, Notifiable;

    public const ROLE_STUDENT = 'student';

    public const ROLE_COUNSELOR = 'counselor';

    public const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'school_id', 'school_joined_at', 'role', 'name', 'email', 'google_id', 'identifier', 'password',
        'must_change_password', 'is_active', 'last_login_at',
    ];

    /** Email & akun Google murid tidak pernah ikut terserialisasi (mis. ke JSON untuk guru). */
    protected $hidden = ['password', 'remember_token', 'email', 'google_id'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'school_joined_at' => 'datetime',
        ];
    }

    public function isStudent(): bool
    {
        return $this->role === self::ROLE_STUDENT;
    }

    public function isCounselor(): bool
    {
        return $this->role === self::ROLE_COUNSELOR;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function homeRoute(): string
    {
        return match ($this->role) {
            self::ROLE_STUDENT => route('student.home'),
            self::ROLE_COUNSELOR => route('counselor.dashboard'),
            default => route('admin.dashboard'),
        };
    }

    /** Murid belum terikat ke sekolah mana pun (baru daftar, atau dilepas admin sekolah). */
    public function needsSchool(): bool
    {
        return $this->isStudent() && $this->school_id === null;
    }

    /** Murid harus menyelesaikan onboarding: setup akun pertama kali, atau memilih sekolah lagi setelah dilepas admin. */
    public function needsOnboarding(): bool
    {
        return $this->isStudent() && ($this->school_id === null || $this->studentProfile?->onboarded_at === null);
    }

    /** Baru bergabung ke sekolahnya (label konteks untuk guru, tanpa membuka identitas). */
    public function isNewSchoolMember(): bool
    {
        return $this->school_joined_at !== null && $this->school_joined_at->gt(now()->subDays(config('welcoming.new_member_days')));
    }

    /** Apakah URL berada di area peran user (mis. /guru/... untuk Guru BK), agar url.intended milik peran lain diabaikan. */
    public function ownsUrl(string $url): bool
    {
        $home = rtrim(parse_url($this->homeRoute(), PHP_URL_PATH) ?? '', '/');
        $path = rtrim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        return $path === $home || str_starts_with($path, $home.'/');
    }

    /** Nama sapaan: nickname guru, atau nama yang diisi murid sendiri di profil. */
    public function displayName(): string
    {
        if ($this->isCounselor()) {
            return $this->counselorProfile?->nickname ?? $this->name;
        }

        return $this->name ?? 'Teman';
    }

    /** Murid sudah mengisi nama di profil (syarat untuk membuka identitas ke guru). */
    public function hasFilledIdentity(): bool
    {
        return filled($this->name);
    }

    public function initial(): string
    {
        $name = preg_replace('/^(Bu|Pak|Ibu|Bapak)\s+/i', '', $this->displayName());

        return mb_strtoupper(mb_substr($name, 0, 1));
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function studentProfile(): HasOne
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function counselorProfile(): HasOne
    {
        return $this->hasOne(CounselorProfile::class);
    }

    public function serviceHours(): HasMany
    {
        return $this->hasMany(CounselorServiceHour::class, 'counselor_id');
    }

    public function assignedConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'counselor_id');
    }

    public function moodEntries(): HasMany
    {
        return $this->hasMany(MoodEntry::class, 'student_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'author_id');
    }

    public function replyTemplates(): HasMany
    {
        return $this->hasMany(ReplyTemplate::class, 'counselor_id');
    }

    public function caseNotes(): HasMany
    {
        return $this->hasMany(CaseNote::class, 'counselor_id');
    }

    public function counselingSlots(): HasMany
    {
        return $this->hasMany(CounselingSlot::class, 'counselor_id');
    }

    public function scopeCounselors($query)
    {
        return $query->where('role', self::ROLE_COUNSELOR);
    }

    public function scopeStudents($query)
    {
        return $query->where('role', self::ROLE_STUDENT);
    }

    /** Batasi ke satu sekolah. Semua daftar guru/murid/statistik wajib lewat sini agar data antar sekolah tidak bercampur. */
    public function scopeInSchool($query, School|int|null $school)
    {
        return $query->where('school_id', $school instanceof School ? $school->id : $school);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Channel broadcast notifikasi per peran. */
    public function receivesBroadcastNotificationsOn(): string
    {
        return ($this->isCounselor() ? 'counselor.' : 'student.').$this->id;
    }
}
