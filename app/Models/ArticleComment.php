<?php

namespace App\Models;

use Database\Factories\ArticleCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Masukan sesama Guru BK untuk pembuat artikel Self-Help. Murid tidak pernah melihatnya. */
class ArticleComment extends Model
{
    /** @use HasFactory<ArticleCommentFactory> */
    use HasFactory;

    protected $fillable = ['article_id', 'counselor_id', 'body'];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
