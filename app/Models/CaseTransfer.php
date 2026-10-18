<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseTransfer extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'from_counselor_id', 'to_counselor_id', 'handover_note'];

    protected function casts(): array
    {
        return [
            'handover_note' => 'encrypted',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function fromCounselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_counselor_id');
    }

    public function toCounselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_counselor_id');
    }
}
