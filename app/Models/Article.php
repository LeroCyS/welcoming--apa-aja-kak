<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id', 'author_id', 'article_category_id', 'type', 'title', 'slug', 'excerpt', 'cover_path', 'body',
        'read_minutes', 'status', 'is_featured', 'published_at', 'view_count',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'read_minutes' => 'integer',
            'view_count' => 'integer',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public static function readMinutesFor(string $html): int
    {
        $text = html_entity_decode(strip_tags(str_replace('<', ' <', $html)));
        $words = count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY));

        return max(1, (int) ceil($words / 200));
    }

    public function coverUrl(): ?string
    {
        if (! $this->cover_path) {
            return null;
        }

        return str_starts_with($this->cover_path, 'images/') ? asset($this->cover_path) : Storage::disk('public')->url($this->cover_path);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ArticleComment::class);
    }

    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class);
    }
}
