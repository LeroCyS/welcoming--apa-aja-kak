<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;

/** Admin tidak pernah punya akses ke percakapan. */
class ConversationPolicy
{
    public function view(User $user, Conversation $c): bool
    {
        return $this->counsel($user, $c) || $this->own($user, $c);
    }

    /** Guru yang sedang di-assign. */
    public function counsel(User $user, Conversation $c): bool
    {
        return $user->isCounselor() && (int) $c->counselor_id === (int) $user->id;
    }

    /**
     * Mengirim pesan/aksi baru. Setelah murid pindah atau dikeluarkan dari sekolah, percakapan menjadi
     * arsip: murid masih bisa membaca, tapi tidak ada pihak yang bisa membalas.
     */
    public function reply(User $user, Conversation $c): bool
    {
        if ($c->isArchived()) {
            return false;
        }

        return $this->counsel($user, $c)
            || ($this->own($user, $c) && (int) $c->school_id === (int) $user->school_id);
    }

    /** Murid pemilik (via student_hash). */
    public function own(User $user, Conversation $c): bool
    {
        return $user->isStudent() && $c->isOwnedBy($user);
    }
}
