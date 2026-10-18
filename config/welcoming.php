<?php

return [
    'identity_hash_key' => env('IDENTITY_HASH_KEY'),

    'follow_up_days' => (int) env('FOLLOW_UP_DAYS', 7),
    'quick_exit_url' => env('QUICK_EXIT_URL', 'https://www.google.com'),
    'voice_retention_days' => (int) env('VOICE_RETENTION_DAYS', 90),
    'voice_max_seconds' => 120,
    'voice_max_kb' => 5120,
    'chat_image_max_kb' => 8192,
    'analytics_min_count' => (int) env('ANALYTICS_MIN_COUNT', 5),
    // Guru dianggap offline bila tidak ada heartbeat selama ini. Heartbeat dikirim tiap 25 detik; batasnya
    // dibuat longgar karena browser memperlambat timer di tab yang sedang di belakang (±1 menit sekali).
    'presence_heartbeat_seconds' => 25,
    'presence_timeout_seconds' => 90,
    'emergency_info_cooldown_hours' => 6,
    'max_new_conversations_per_day' => 5,
    'pin_idle_minutes' => 15,
    // Selama sekian hari setelah bergabung, cerita murid diberi label "anggota baru" untuk guru.
    'new_member_days' => 7,

    'alias_animals' => ['Kucing', 'Rubah', 'Panda', 'Burung Hantu', 'Kelinci', 'Penyu', 'Lumba-lumba', 'Rusa'],
    'alias_colors' => ['Oranye', 'Biru', 'Hijau', 'Ungu', 'Kuning', 'Merah Muda', 'Toska', 'Cokelat'],

    // Kunci = bentuk baku, nilai = varian yang dinormalisasi ke bentuk baku.
    'slang_map' => [
        'gak' => ['gk', 'ga', 'gak', 'nggak', 'ngga', 'engga', 'enggak'],
        'pengen' => ['pgn', 'pengin', 'pingin', 'pengen', 'pengn'],
        'aja' => ['aj', 'ajah', 'aja'],
        'banget' => ['bgt', 'banget'],
        'mau' => ['mo', 'mau'],
        'udah' => ['udh', 'udah', 'sudah'],
        'dengan' => ['dgn', 'dengan'],
    ],

    'mood_article_map' => [
        'anxious' => ['overthinking', 'stres-ujian'],
        'sad' => ['percaya-diri', 'pertemanan'],
        'angry' => ['pertemanan', 'keluarga'],
    ],

    'topic_programs' => [
        'akademik' => 'Seminar manajemen stres ujian & teknik belajar efektif',
        'bullying' => 'Kampanye anti-perundungan & pelatihan peer support',
        'keluarga' => 'Program parenting / komunikasi orang tua-anak',
        'pertemanan' => 'Kegiatan bonding kelas & pelatihan komunikasi',
        'asmara' => 'Edukasi relasi sehat',
        'diri-sendiri' => 'Workshop self-esteem & regulasi emosi',
        'masa-depan' => 'Bimbingan karier & sesi alumni',
        'belum-tahu' => 'Sesi check-in kelas oleh Guru BK',
    ],

    'ai' => [
        'summary_driver' => env('AI_SUMMARY_DRIVER', 'fake'),
        'transcribe_driver' => env('AI_TRANSCRIBE_DRIVER', 'fake'),
        'api_key' => env('AI_API_KEY'),
        'model' => env('AI_MODEL'),
        'transcribe_model' => env('AI_TRANSCRIBE_MODEL'),
        'base_url' => env('AI_BASE_URL'),
    ],
];
