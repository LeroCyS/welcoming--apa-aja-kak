<?php

namespace App\Support;

/**
 * Tampilan tiap jenis notifikasi (label kelompok, ikon, warna) untuk lonceng, halaman Notifikasi, dan toast.
 * Teks yang dikirim ke perangkat murid selalu netral: tanpa kata "BK", "konseling", maupun isi pesan.
 */
class NotificationKind
{
    /** @var array<string, array{label: string, icon: string, tone: string}> */
    public const KINDS = [
        'new_message' => ['label' => 'Pesan baru', 'icon' => 'chat', 'tone' => 'sage'],
        'counselor_reply' => ['label' => 'Balasan', 'icon' => 'chat-heart', 'tone' => 'terracotta'],
        'new_conversation' => ['label' => 'Cerita baru', 'icon' => 'chat-heart', 'tone' => 'terracotta'],
        'critical' => ['label' => 'Kasus kritis', 'icon' => 'alert', 'tone' => 'danger'],
        'identity_request' => ['label' => 'Pertanyaan', 'icon' => 'user-check', 'tone' => 'sage'],
        'identity_revealed' => ['label' => 'Identitas', 'icon' => 'unlock', 'tone' => 'sand'],
        'identity_declined' => ['label' => 'Identitas', 'icon' => 'lock', 'tone' => 'sage'],
        'session_invite' => ['label' => 'Undangan', 'icon' => 'calendar', 'tone' => 'sand'],
        'invitation_accepted' => ['label' => 'Jadwal', 'icon' => 'calendar', 'tone' => 'sage'],
        'invitation_declined' => ['label' => 'Jadwal', 'icon' => 'calendar', 'tone' => 'sand'],
        'session_rescheduled' => ['label' => 'Jadwal', 'icon' => 'calendar', 'tone' => 'sand'],
        'session_cancelled' => ['label' => 'Jadwal', 'icon' => 'calendar', 'tone' => 'sand'],
        'session_reminder' => ['label' => 'Pengingat', 'icon' => 'clock', 'tone' => 'sand'],
        'follow_up' => ['label' => 'Kabar', 'icon' => 'heart', 'tone' => 'sand'],
        'follow_up_worse' => ['label' => 'Follow-up', 'icon' => 'alert', 'tone' => 'danger'],
        'case_transfer' => ['label' => 'Serah terima', 'icon' => 'transfer', 'tone' => 'sage'],
        'summary_ready' => ['label' => 'Ringkasan AI', 'icon' => 'sparkle', 'tone' => 'sage'],
        'story_pending' => ['label' => 'Cerita Teman', 'icon' => 'people-heart', 'tone' => 'terracotta'],
        'article_comment' => ['label' => 'Komentar artikel', 'icon' => 'book', 'tone' => 'sage'],
        'school_transfer' => ['label' => 'Pindah sekolah', 'icon' => 'home', 'tone' => 'sand'],
    ];

    /** @return array{label: string, icon: string, tone: string} */
    public static function for(string $kind): array
    {
        return self::KINDS[$kind] ?? ['label' => 'Notifikasi', 'icon' => 'bell', 'tone' => 'sage'];
    }
}
