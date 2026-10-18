<?php

namespace App\Models;

use App\Support\IdentityHasher;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'new' => 'Baru',
        'monitoring' => 'Dalam Pemantauan',
        'referred_parent' => 'Rujuk ke Orang Tua',
        'resolved' => 'Selesai',
    ];

    /** Urgensi dinilai Guru BK. Urutan dari paling mendesak. */
    public const URGENCY_LABELS = [
        'urgent' => 'Mendesak',
        'important' => 'Perlu perhatian',
        'normal' => 'Biasa',
    ];

    /** Kunci tampilan/filter untuk kasus yang urgensinya belum dinilai guru (kolom `urgency` NULL). */
    public const UNASSESSED = 'unassessed';

    public const UNASSESSED_LABEL = 'Belum dinilai';

    protected $fillable = [
        'uuid', 'school_id', 'counselor_id', 'student_ref', 'student_hash', 'student_id', 'grade_level',
        'is_anonymous', 'anonymous_alias', 'anonymous_avatar', 'identity_revealed_at', 'revealed_to_counselor_id',
        'urgency', 'urgency_assessed_at', 'priority_score', 'status', 'is_emergency', 'last_message_at', 'last_student_message_at',
        'last_counselor_message_at', 'first_response_at', 'last_auto_reply_at', 'resolved_at',
        'from_new_member', 'student_left_at', 'prank_reported_at', 'prank_reported_by', 'board_column_id',
    ];

    protected $hidden = ['student_ref', 'student_hash', 'student_id', 'grade_level'];

    protected function casts(): array
    {
        return [
            'student_ref' => 'encrypted',
            'is_anonymous' => 'boolean',
            'is_emergency' => 'boolean',
            'from_new_member' => 'boolean',
            'student_left_at' => 'datetime',
            'prank_reported_at' => 'datetime',
            'grade_level' => 'integer',
            'priority_score' => 'integer',
            'urgency_assessed_at' => 'datetime',
            'identity_revealed_at' => 'datetime',
            'last_message_at' => 'datetime',
            'last_student_message_at' => 'datetime',
            'last_counselor_message_at' => 'datetime',
            'first_response_at' => 'datetime',
            'last_auto_reply_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    // ---- Scopes ----

    public function scopeForStudent($query, User $user)
    {
        return $query->where('student_hash', IdentityHasher::hash($user->id));
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'resolved');
    }

    public function scopeInSchool($query, School|int $school)
    {
        return $query->where('school_id', $school instanceof School ? $school->id : $school);
    }

    /** Murid sudah pindah/dikeluarkan dari sekolah ini: percakapan tinggal arsip yang hanya bisa dibaca. */
    public function isArchived(): bool
    {
        return $this->student_left_at !== null;
    }

    // ---- Identitas ----

    public function isOwnedBy(User $user): bool
    {
        return $user->isStudent() && hash_equals($this->student_hash, IdentityHasher::hash($user->id));
    }

    /** Apakah viewer (guru) boleh melihat identitas asli murid. */
    public function identityVisibleTo(User $viewer): bool
    {
        if ($this->isOwnedBy($viewer)) {
            return true;
        }

        if (! $viewer->isCounselor() || (int) $this->counselor_id !== (int) $viewer->id) {
            return false;
        }

        return ! $this->is_anonymous || (int) $this->revealed_to_counselor_id === (int) $viewer->id;
    }

    /** Apakah murid masih anonim terhadap guru yang menangani saat ini. */
    public function isAnonymousToCurrentCounselor(): bool
    {
        return $this->is_anonymous && (int) $this->revealed_to_counselor_id !== (int) $this->counselor_id;
    }

    public function displayNameFor(User $viewer): string
    {
        if ($this->identityVisibleTo($viewer) && filled($this->revealedStudent?->name)) {
            return $this->revealedStudent->name;
        }

        return $this->anonymous_alias;
    }

    public function studentStatusLabel(): string
    {
        return $this->status === 'resolved' ? 'Selesai' : 'Sedang berjalan';
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    /**
     * Gaya tampilan urgensi untuk inbox/kasus: [label, kelas chip, kelas titik].
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function urgencyStyles(): array
    {
        return [
            'urgent' => [self::URGENCY_LABELS['urgent'], 'bg-terracotta-tint text-terracotta-ink', 'bg-terracotta-deep'],
            'important' => [self::URGENCY_LABELS['important'], 'bg-sand-tint text-sand-ink', 'bg-sand-dot'],
            'normal' => [self::URGENCY_LABELS['normal'], 'bg-sage-tint text-sage-ink', 'bg-sage-deep'],
            self::UNASSESSED => [self::UNASSESSED_LABEL, 'bg-white text-ink-soft border border-dashed border-toggle-off', 'bg-[#B9AFA0]'],
        ];
    }

    public function urgencyKey(): string
    {
        return $this->urgency ?? self::UNASSESSED;
    }

    public function urgencyLabel(): string
    {
        return self::URGENCY_LABELS[$this->urgency] ?? self::UNASSESSED_LABEL;
    }

    // ---- Relasi ----

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function prankReporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prank_reported_by');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function revealedStudent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class)->orderBy('sort_order');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function flags(): HasMany
    {
        return $this->hasMany(ConversationFlag::class);
    }

    public function unreviewedFlags(): HasMany
    {
        return $this->hasMany(ConversationFlag::class)->whereNull('reviewed_at');
    }

    /** Catatan privat, selalu di-scope ke guru yang sedang login. */
    public function caseNotes(): HasMany
    {
        return $this->hasMany(CaseNote::class)->where('counselor_id', auth()->id() ?? 0)->latest();
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(CaseStatusLog::class)->latest('id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(CaseTransfer::class)->latest('id');
    }

    public function identityRequests(): HasMany
    {
        return $this->hasMany(IdentityRequest::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(SessionInvitation::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(CounselingSession::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function summaries(): HasMany
    {
        return $this->hasMany(ConversationSummary::class);
    }

    public function latestSummary(): HasOne
    {
        return $this->hasOne(ConversationSummary::class)->latestOfMany();
    }
}
