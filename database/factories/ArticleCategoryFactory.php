<?php

namespace Database\Factories;

use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ArticleCategory> */
class ArticleCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->word()),
            'slug' => fn (array $a) => Str::slug($a['name']),
            'icon' => 'book',
            'sort_order' => 0,
        ];
    }
}
