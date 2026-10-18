<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function view(User $user, Article $article): bool
    {
        return $user->isCounselor() || ($article->status === 'published' && $user->isStudent());
    }

    /** Hanya pembuat artikel yang boleh mengubah, menerbitkan, atau menghapusnya. Guru lain cukup melihat & berkomentar. */
    public function manage(User $user, ?Article $article = null): bool
    {
        if (! $user->isCounselor()) {
            return false;
        }

        return $article === null || (int) $article->author_id === (int) $user->id;
    }
}
