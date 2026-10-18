<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory;

    /** Batas waktu seperti WhatsApp (menit sejak dikirim). */
    /** Batas waktu mengikuti WhatsApp: edit 15 menit, hapus untuk semua orang 2 hari 12 jam. */
    public const EDIT_MINUTES = 15;

    public const RETRACT_MINUTES = 60 * 60;

    public const RETRACT_LABEL = '2 hari 12 jam';

    /** Jenis pesan buatan pengguna yang boleh dihapus; hanya teks yang boleh diedit. */
    public const DELETABLE_TYPES = ['text', 'image', 'voice', 'song', 'article', 'mood_share'];

    /** Keyword dengan severity ini ke atas dianggap kritis: isi aslinya tetap terlihat oleh Guru BK walau diedit/dihapus. */
    public const CRITICAL_SEVERITY = 3;

    protected $fillable = ['conversation_id', 'sender_type', 'sender_id', 'type', 'body', 'original_body', 'meta', 'delivered_at', 'read_at', 'edited_at', 'retracted_at'];

    protected function casts(): array
    {
        return [
            'body' => 'encrypted',
            'original_body' => 'encrypted',
            'meta' => 'array',
            'delivered_at' => 'datetime',
            'read_at' => 'datetime',
            'edited_at' => 'datetime',
            'retracted_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function voiceNote(): HasOne
    {
        return $this->hasOne(VoiceNote::class);
    }

    public function hides(): HasMany
    {
        return $this->hasMany(MessageHide::class);
    }

    public function flags(): HasMany
    {
        return $this->hasMany(ConversationFlag::class);
    }

    /** Pesan yang tidak disembunyikan oleh pengguna ini ("hapus untuk saya"). */
    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $query->whereDoesntHave('hides', fn (Builder $q) => $q->where('user_id', $user->id));
    }

    /** Pengirim pesan ini adalah pengguna tersebut (murid via kepemilikan percakapan, guru via sender_id). */
    public function isSentBy(User $user): bool
    {
        if ($user->isStudent()) {
            return $this->sender_type === 'student' && $this->conversation->isOwnedBy($user);
        }

        return $user->isCounselor() && $this->sender_type === 'counselor' && (int) $this->sender_id === (int) $user->id;
    }

    public function isRetracted(): bool
    {
        return $this->retracted_at !== null;
    }

    /** Pesan murid yang memicu kata kunci kritis. */
    public function isCritical(): bool
    {
        return $this->sender_type === 'student'
            && $this->flags()->where('severity', '>=', self::CRITICAL_SEVERITY)->exists();
    }

    public function canBeEditedBy(User $user): bool
    {
        return $this->type === 'text' && ! $this->isRetracted() && $this->isSentBy($user)
            && $this->created_at->gt(now()->subMinutes(self::EDIT_MINUTES));
    }

    public function canBeRetractedBy(User $user): bool
    {
        return in_array($this->type, self::DELETABLE_TYPES, true) && ! $this->isRetracted() && $this->isSentBy($user)
            && $this->created_at->gt(now()->subMinutes(self::RETRACT_MINUTES));
    }

    public function isFromStudent(): bool
    {
        return $this->sender_type === 'student';
    }

    public function isSystemLike(): bool
    {
        return in_array($this->type, ['auto_reply', 'emergency_info', 'system'], true);
    }

    /** Potongan teks untuk daftar percakapan & notifikasi (teks polos; ikonnya lewat previewIcon()). */
    public function preview(int $limit = 80): string
    {
        if ($this->isRetracted()) {
            return 'Pesan dihapus';
        }
        $text = match ($this->type) {
            'voice' => 'Pesan suara',
            'image' => 'Foto'.(filled($this->body) ? ': '.$this->body : ''),
            'song' => $this->meta['song']['title'] ?? 'Lagu dibagikan',
            'article' => 'Artikel dibagikan',
            'session_invite' => 'Undangan ngobrol langsung',
            'identity_request' => 'Permintaan buka identitas',
            'mood_share' => 'Catatan mood dibagikan',
            'follow_up' => 'Hai, gimana kabarmu sekarang?',
            'emergency_info' => 'Info kontak bantuan darurat',
            default => (string) $this->body,
        };

        return Str::limit($text, $limit);
    }

    /** Nama ikon garis (komponen x-icon) untuk pratinjau pesan non-teks; null untuk pesan teks biasa. */
    public function previewIcon(): ?string
    {
        if ($this->isRetracted()) {
            return 'trash';
        }

        return match ($this->type) {
            'voice' => 'mic',
            'image' => 'image',
            'song' => 'play',
            'article' => 'book',
            'session_invite' => 'calendar',
            'identity_request' => 'user-check',
            'mood_share' => 'mood',
            'follow_up' => 'heart',
            'emergency_info' => 'phone',
            default => null,
        };
    }
}
