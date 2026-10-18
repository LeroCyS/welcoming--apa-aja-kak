<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MoodPromptLog extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'shown_at', 'response'];

    protected function casts(): array
    {
        return [
            'shown_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
