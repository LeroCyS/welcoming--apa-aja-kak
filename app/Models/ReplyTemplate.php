<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReplyTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['counselor_id', 'topic_id', 'title', 'shortcut', 'body'];

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
