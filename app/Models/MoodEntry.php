<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodEntry extends Model
{
    use HasFactory;

    public const MOODS = ['happy', 'neutral', 'sad', 'anxious', 'angry'];

    public const NEGATIVE = ['sad', 'anxious', 'angry'];

    public const LABELS = ['happy' => 'Senang', 'neutral' => 'Biasa', 'sad' => 'Sedih', 'anxious' => 'Cemas', 'angry' => 'Marah'];

    public const SCORES = ['happy' => 5, 'neutral' => 4, 'anxious' => 2, 'sad' => 2, 'angry' => 1];

    public const COLORS = ['happy' => '#4F6645', 'neutral' => '#E8E3D8', 'sad' => '#8A6A3B', 'anxious' => '#DE9A89', 'angry' => '#B5523F'];

    protected $fillable = ['student_id', 'entry_date', 'mood', 'note', 'song', 'shared_at', 'shared_conversation_id'];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'note' => 'encrypted',
            'song' => 'array',
            'shared_at' => 'datetime',
        ];
    }

    public function isNegative(): bool
    {
        return in_array($this->mood, self::NEGATIVE, true);
    }

    public function label(): string
    {
        return self::LABELS[$this->mood];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function sharedConversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'shared_conversation_id');
    }
}
