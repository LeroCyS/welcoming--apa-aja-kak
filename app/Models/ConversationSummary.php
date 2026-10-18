<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationSummary extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'requested_by', 'status', 'content', 'last_message_id', 'message_count', 'driver', 'error'];

    protected function casts(): array
    {
        return ['content' => 'encrypted:array', 'message_count' => 'integer'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
