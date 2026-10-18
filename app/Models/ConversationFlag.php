<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationFlag extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'message_id', 'sensitive_keyword_id', 'severity', 'reviewed_at', 'reviewed_by'];

    protected function casts(): array
    {
        return [
            'severity' => 'integer',
            'reviewed_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(SensitiveKeyword::class, 'sensitive_keyword_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
