<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Article> */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => fn () => School::query()->value('id') ?? School::factory(),
            'author_id' => User::factory()->counselor(),
            'article_category_id' => ArticleCategory::factory(),
            'type' => 'article',
            'title' => $t = fake()->sentence(4),
            'slug' => Str::slug($t).'-'.Str::random(4),
            'excerpt' => fake()->sentence(12),
            'body' => '<p>'.fake()->paragraph(8).'</p>',
            'read_minutes' => 2,
            'status' => 'published',
            'is_featured' => false,
            'published_at' => now()->subDay(),
        ];
    }
}
