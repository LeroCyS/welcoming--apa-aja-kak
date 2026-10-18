<?php

namespace App\Policies;

use App\Models\CounselingSession;
use App\Models\User;

class CounselingSessionPolicy
{
    public function view(User $user, CounselingSession $session): bool
    {
        return ($user->isStudent() && (int) $session->student_id === (int) $user->id)
            || ($user->isCounselor() && (int) $session->counselor_id === (int) $user->id);
    }

    public function reschedule(User $user, CounselingSession $session): bool
    {
        return $user->isStudent() && (int) $session->student_id === (int) $user->id;
    }

    public function manage(User $user, CounselingSession $session): bool
    {
        return $user->isCounselor() && (int) $session->counselor_id === (int) $user->id;
    }
}
