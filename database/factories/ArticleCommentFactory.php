<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ArticleComment> */
class ArticleCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'article_id' => Article::factory(),
            'counselor_id' => User::factory()->counselor(),
            'body' => fake()->sentence(10),
        ];
    }
}
