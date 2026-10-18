<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoryReaction extends Model
{
    protected $fillable = ['story_post_id', 'reactor_hash', 'type'];

    protected $hidden = ['reactor_hash'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(StoryPost::class, 'story_post_id');
    }
}
