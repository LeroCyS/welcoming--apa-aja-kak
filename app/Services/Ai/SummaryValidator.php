<?php

namespace App\Services\Ai;

use App\Models\Conversation;
use InvalidArgumentException;

class SummaryValidator
{
    public const LEVELS = ['rendah', 'sedang', 'tinggi'];

    /** Validasi & normalisasi struktur JSON ringkasan (Bagian 10.2). */
    public static function validate(mixed $data): array
    {
        if (! is_array($data)) {
            throw new InvalidArgumentException('Format ringkasan tidak valid.');
        }
        $list = fn ($v) => array_values(array_filter(array_map(fn ($x) => is_scalar($x) ? trim((string) $x) : '', is_array($v) ? $v : []), fn ($x) => $x !== ''));

        $ringkasan = trim((string) ($data['ringkasan'] ?? ''));
        $level = strtolower((string) ($data['tingkat_risiko']['level'] ?? ''));
        if ($ringkasan === '' || ! in_array($level, self::LEVELS, true)) {
            throw new InvalidArgumentException('Ringkasan tidak lengkap.');
        }

        return [
            'ringkasan' => $ringkasan,
            'inti_masalah' => $list($data['inti_masalah'] ?? []),
            'emosi_terdeteksi' => $list($data['emosi_terdeteksi'] ?? []),
            'tindakan_sudah_dilakukan' => $list($data['tindakan_sudah_dilakukan'] ?? []),
            'saran_langkah_berikutnya' => $list($data['saran_langkah_berikutnya'] ?? []),
            'tingkat_risiko' => ['level' => $level, 'alasan' => trim((string) ($data['tingkat_risiko']['alasan'] ?? ''))],
        ];
    }

    /** Ambil objek JSON pertama dari teks respons LLM. */
    public static function extractJson(string $text): array
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false) {
            throw new InvalidArgumentException('Respons AI bukan JSON.');
        }
        $decoded = json_decode(substr($text, $start, $end - $start + 1), true);
        if (! is_array($decoded)) {
            throw new InvalidArgumentException('Respons AI bukan JSON yang valid.');
        }

        return $decoded;
    }

    public static function systemPrompt(): string
    {
        return <<<'TXT'
Kamu adalah asisten untuk Guru BK (Bimbingan Konseling) di sebuah sekolah menengah di Indonesia.
Tugasmu meringkas percakapan antara murid dan Guru BK secara objektif agar guru cepat memahami situasinya.
Aturan:
- Tulis dalam Bahasa Indonesia yang ringkas dan netral.
- Jangan mendiagnosis kondisi kejiwaan apa pun.
- Jangan menebak atau menyimpulkan identitas murid.
- Tandai tingkat risiko "tinggi" jika ada indikasi menyakiti diri, keinginan mengakhiri hidup, kekerasan, atau pelecehan.
- Output HANYA berupa JSON valid dengan skema persis berikut, tanpa teks lain:
{"ringkasan":"2-4 kalimat","inti_masalah":["..."],"emosi_terdeteksi":["..."],"tindakan_sudah_dilakukan":["..."],"saran_langkah_berikutnya":["..."],"tingkat_risiko":{"level":"rendah|sedang|tinggi","alasan":"..."}}
TXT;
    }

    public static function userPrompt(array $messages, array $context): string
    {
        $lines = array_map(fn ($m) => "{$m['role']}: {$m['text']}", $messages);

        return 'Topik: '.implode(', ', $context['topics'] ?? [])."\n"
            .'Urgensi menurut penilaian Guru BK: '.(Conversation::URGENCY_LABELS[$context['urgency'] ?? ''] ?? Conversation::UNASSESSED_LABEL)."\n\n"
            ."Percakapan:\n".implode("\n", $lines);
    }
}
