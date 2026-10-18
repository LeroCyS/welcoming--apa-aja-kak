<?php

namespace App\Models;

use App\Support\IdentityHasher;
use Database\Factories\StoryPostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cerita Teman: cerita anonim murid untuk dibaca murid lain di sekolah yang sama.
 * Penulis hanya disimpan sebagai HMAC, ditinjau Guru BK sebelum tampil, dan murid lain hanya bisa memberi reaksi.
 */
class StoryPost extends Model
{
    /** @use HasFactory<StoryPostFactory> */
    use HasFactory;

    public const MAX_PER_DAY = 3;

    /** Reaksi dukungan: kunci => [label, ikon]. Tidak ada komentar teks agar aman dari perundungan. */
    public const REACTIONS = [
        'peluk' => ['Peluk dari jauh', 'heart'],
        'kuat' => ['Kamu kuat', 'sparkle'],
        'sama' => ['Aku juga pernah', 'people'],
    ];

    public const STATUS_LABELS = [
        'pending' => 'Menunggu tinjauan',
        'approved' => 'Tampil',
        'rejected' => 'Tidak ditampilkan',
    ];

    protected $fillable = ['school_id', 'author_hash', 'display_label', 'body', 'song', 'status', 'reject_reason', 'moderated_by', 'moderated_at'];

    protected $hidden = ['author_hash'];

    protected function casts(): array
    {
        return [
            'body' => 'encrypted',
            'song' => 'array',
            'moderated_at' => 'datetime',
        ];
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeByAuthor($query, User $user)
    {
        return $query->where('author_hash', IdentityHasher::hash($user->id));
    }

    public function isWrittenBy(User $user): bool
    {
        return $user->isStudent() && hash_equals($this->author_hash, IdentityHasher::hash($user->id));
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderated_by');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(StoryReaction::class);
    }
}
