<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\User;

/** Mendekripsi student_ref → User. Hanya dipakai di sisi server (notifikasi, aksi murid). */
class StudentResolver
{
    public function resolve(Conversation $conversation): ?User
    {
        $id = (int) $conversation->student_ref;

        return $id ? User::with('studentProfile')->find($id) : null;
    }
}
