<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CounselingSlot extends Model
{
    use HasFactory;

    protected $fillable = ['counselor_id', 'start_at', 'end_at', 'location', 'status'];

    protected function casts(): array
    {
        return ['start_at' => 'datetime', 'end_at' => 'datetime'];
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function invitations(): BelongsToMany
    {
        return $this->belongsToMany(SessionInvitation::class, 'invitation_slot');
    }

    public function session(): HasOne
    {
        return $this->hasOne(CounselingSession::class)->where('status', '!=', 'cancelled');
    }

    public function label(): string
    {
        $tz = School::current()->timezone;

        return $this->start_at->timezone($tz)->translatedFormat('l, j M').' · '.$this->start_at->timezone($tz)->format('H.i').'-'.$this->end_at->timezone($tz)->format('H.i');
    }
}
