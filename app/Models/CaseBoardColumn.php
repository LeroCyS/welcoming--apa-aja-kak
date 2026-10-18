<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kolom kanban pribadi Guru BK. Kartu di kolom ini tetap memakai status kasusnya. */
class CaseBoardColumn extends Model
{
    /** Warna yang bisa dipilih: [latar kolom, titik/aksen]. */
    public const COLORS = [
        'sand' => ['bg-sand-tint/70', 'bg-sand'],
        'sage' => ['bg-sage-tint/80', 'bg-sage-deep'],
        'terracotta' => ['bg-terracotta-tint2', 'bg-terracotta'],
        'ink' => ['bg-cream-deep/70', 'bg-ink-soft'],
    ];

    public const MAX_PER_COUNSELOR = 8;

    protected $fillable = ['counselor_id', 'name', 'color', 'position'];

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'board_column_id');
    }
}
