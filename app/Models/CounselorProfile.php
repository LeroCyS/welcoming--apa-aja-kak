<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CounselorProfile extends Model
{
    use HasFactory;

    /** Teks bawaan auto-reply. {guru} = nama panggilan, {jam_layanan} = jam layanan berikutnya (diisi otomatis). */
    public const DEFAULT_AUTO_REPLY = 'Hai, makasih sudah cerita. {guru} sedang tidak bisa membalas sekarang, tapi pesanmu sudah aman tersimpan dan akan dibalas di jam layanan berikutnya: {jam_layanan}. Sambil menunggu, kamu bisa baca-baca di Ruang Self-Help ya.';

    /** Teks bawaan versi lama, dianggap sama dengan "pakai teks bawaan". */
    public const LEGACY_AUTO_REPLY = 'Hai, makasih sudah cerita. Aku sedang tidak online sekarang, tapi pesanmu sudah aman tersimpan dan akan kubalas di jam layanan berikutnya. Sambil menunggu, kamu bisa baca-baca di Ruang Self-Help ya.';

    protected $fillable = [
        'user_id', 'nickname', 'photo_path', 'gender', 'hobbies', 'favorite_food',
        'intro_message', 'offline_auto_reply', 'is_online', 'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Teks auto-reply yang dipakai: tulisan guru sendiri, atau teks bawaan bila kosong/masih teks bawaan lama. */
    public function autoReplyTemplate(): string
    {
        $text = trim((string) $this->offline_auto_reply);

        return $text === '' || $text === self::LEGACY_AUTO_REPLY ? self::DEFAULT_AUTO_REPLY : $text;
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }
}
