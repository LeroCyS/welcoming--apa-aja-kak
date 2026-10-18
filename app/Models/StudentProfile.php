<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'class_name', 'grade_level', 'pin_hash', 'pin_enabled', 'pin_failed_attempts',
        'pin_locked_until', 'notifications_enabled', 'mood_prompt_enabled', 'mood_signal_enabled',
        'onboarded_at', 'tour_completed_at',
    ];

    protected $hidden = ['pin_hash'];

    protected function casts(): array
    {
        return [
            'grade_level' => 'integer',
            'pin_enabled' => 'boolean',
            'pin_failed_attempts' => 'integer',
            'pin_locked_until' => 'datetime',
            'notifications_enabled' => 'boolean',
            'mood_prompt_enabled' => 'boolean',
            'mood_signal_enabled' => 'boolean',
            'onboarded_at' => 'datetime',
            'tour_completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
