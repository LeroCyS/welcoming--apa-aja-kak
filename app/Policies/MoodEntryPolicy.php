<?php

namespace App\Policies;

use App\Models\MoodEntry;
use App\Models\User;

class MoodEntryPolicy
{
    public function view(User $user, MoodEntry $entry): bool
    {
        return $user->isStudent() && (int) $entry->student_id === (int) $user->id;
    }

    public function share(User $user, MoodEntry $entry): bool
    {
        return $this->view($user, $entry);
    }
}
