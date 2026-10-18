<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselingSession extends Model
{
    use HasFactory;

    public const STATUS_LABELS = [
        'scheduled' => 'Terjadwal',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'no_show' => 'Tidak hadir',
    ];

    protected $fillable = [
        'uuid', 'conversation_id', 'session_invitation_id', 'counseling_slot_id', 'counselor_id', 'student_id',
        'status', 'reschedule_count',
    ];

    protected function casts(): array
    {
        return ['reschedule_count' => 'integer'];
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(SessionInvitation::class, 'session_invitation_id');
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(CounselingSlot::class, 'counseling_slot_id');
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
