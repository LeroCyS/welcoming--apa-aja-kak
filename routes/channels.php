<?php

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Percakapan: guru yang di-assign ATAU murid pemilik (via student_hash).
Broadcast::channel('conversation.{uuid}', function (User $user, string $uuid) {
    $c = Conversation::where('uuid', $uuid)->first();
    if (! $c) {
        return false;
    }

    return ($user->isCounselor() && (int) $c->counselor_id === (int) $user->id) || $c->isOwnedBy($user);
});

Broadcast::channel('counselor.{userId}', fn (User $user, int $userId) => $user->isCounselor() && $user->id === $userId);

Broadcast::channel('student.{userId}', fn (User $user, int $userId) => $user->isStudent() && $user->id === $userId);

// Status online Guru BK: semua user di sekolah yang sama (murid melihat perubahan saat itu juga).
Broadcast::channel('school.{schoolId}', fn (User $user, int $schoolId) => (int) $user->school_id === $schoolId);
