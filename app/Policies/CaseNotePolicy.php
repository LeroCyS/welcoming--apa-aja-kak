<?php

namespace App\Policies;

use App\Models\CaseNote;
use App\Models\User;

class CaseNotePolicy
{
    public function update(User $user, CaseNote $note): bool
    {
        return $user->isCounselor() && (int) $note->counselor_id === (int) $user->id;
    }

    public function delete(User $user, CaseNote $note): bool
    {
        return $this->update($user, $note);
    }
}
