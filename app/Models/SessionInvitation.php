<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SessionInvitation extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'counselor_id', 'message', 'status', 'expires_at', 'responded_at'];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'responded_at' => 'datetime'];
    }

    public function isOpen(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function slots(): BelongsToMany
    {
        return $this->belongsToMany(CounselingSlot::class, 'invitation_slot')->orderBy('start_at');
    }

    public function session(): HasOne
    {
        return $this->hasOne(CounselingSession::class);
    }
}
