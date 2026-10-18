<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;

/**
 * Minimisasi data sebelum dikirim ke penyedia AI (Bagian 10.3).
 * Hanya peran + teks/transkrip, nama topik, dan urgensi. Tanpa nama, kelas, NISN, alias, nama guru, grade_level.
 */
class AiPayloadSanitizer
{
    public const MASK = '[disamarkan]';

    public function __construct(protected StudentResolver $resolver) {}

    /** @return array{messages: list<array{role: string, text: string}>, context: array} */
    public function build(Conversation $conversation, iterable $messages): array
    {
        $names = $this->sensitiveNames($conversation);

        $out = [];
        foreach ($messages as $m) {
            /** @var Message $m */
            $text = match ($m->type) {
                'voice' => $m->voiceNote?->transcript_status === 'done' ? (string) $m->voiceNote->transcript : '',
                'text', 'mood_share', 'system', 'follow_up' => (string) $m->body,
                default => '',
            };
            if (trim($text) === '') {
                continue;
            }
            $role = ['student' => 'Murid', 'counselor' => 'Guru', 'system' => 'Sistem'][$m->sender_type];
            $out[] = ['role' => $role, 'text' => $this->clean($text, $names)];
        }

        return [
            'messages' => $out,
            'context' => [
                'topics' => $conversation->topics->pluck('name')->all(),
                'topic_slugs' => $conversation->topics->pluck('slug')->all(),
                'urgency' => $conversation->urgencyKey(),
                'max_flag_severity' => (int) $conversation->flags()->max('severity'),
            ],
        ];
    }

    /** @param list<string> $names */
    public function clean(string $text, array $names = []): string
    {
        // email
        $text = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', self::MASK, $text);
        // nomor telepon Indonesia
        $text = preg_replace('/(\+62|62|0)8\d{7,11}/', self::MASK, $text);
        // deretan angka ≥ 8 digit (NISN, NIK, dll.)
        $text = preg_replace('/\d{8,}/', self::MASK, $text);
        // nama murid/guru yang muncul di teks
        foreach ($names as $name) {
            if (mb_strlen($name) >= 3) {
                $text = preg_replace('/(?<![\p{L}])'.preg_quote($name, '/').'(?![\p{L}])/iu', self::MASK, $text);
            }
        }

        return $text;
    }

    /** @return list<string> */
    protected function sensitiveNames(Conversation $c): array
    {
        $names = [$c->anonymous_alias];
        $student = $this->resolver->resolve($c);
        if ($student) {
            $names[] = $student->name;
            $names = array_merge($names, explode(' ', (string) $student->name));
            if ($student->studentProfile) {
                $names[] = $student->studentProfile->class_name;
            }
            $names[] = $student->identifier;
        }
        foreach (User::counselors()->with('counselorProfile')->get() as $counselor) {
            $names[] = $counselor->name;
            $names = array_merge($names, explode(' ', $counselor->name));
            if ($counselor->counselorProfile) {
                $names[] = $counselor->counselorProfile->nickname;
            }
        }
        $stop = ['bu', 'pak', 'ibu', 'bapak', 'kak'];
        $names = array_filter(array_unique($names), fn ($n) => $n && ! in_array(mb_strtolower($n), $stop, true));
        usort($names, fn ($a, $b) => mb_strlen($b) <=> mb_strlen($a));

        return array_values($names);
    }
}
