<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoiceNote extends Model
{
    use HasFactory;

    public const MODE_LABELS = [
        'none' => 'Tanpa Samaran',
        'deep' => 'Suara Rendah',
        'high' => 'Suara Tinggi',
        'robot' => 'Robot',
    ];

    protected $fillable = [
        'message_id', 'conversation_id', 'file_path', 'mime_type', 'size_bytes', 'duration_seconds',
        'disguise_mode', 'transcript', 'transcript_status', 'audio_deleted_at',
    ];

    protected $hidden = ['file_path'];

    protected function casts(): array
    {
        return [
            'transcript' => 'encrypted',
            'audio_deleted_at' => 'datetime',
            'size_bytes' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
