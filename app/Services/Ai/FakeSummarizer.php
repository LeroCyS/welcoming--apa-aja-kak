<?php

namespace App\Services\Ai;

use App\Contracts\Summarizer;
use Illuminate\Support\Str;

/** Ringkasan ekstraktif sederhana agar aplikasi bisa didemo tanpa API key. */
class FakeSummarizer implements Summarizer
{
    protected const EMOTIONS = [
        'sedih' => ['sedih', 'nangis', 'kecewa', 'hancur', 'galau'],
        'cemas' => ['cemas', 'takut', 'khawatir', 'deg', 'panik', 'overthinking', 'kepikiran'],
        'marah' => ['marah', 'kesal', 'kesel', 'benci', 'emosi'],
        'takut' => ['takut', 'ngeri', 'diancam'],
        'capek' => ['capek', 'lelah', 'cape', 'gak kuat', 'numpuk'],
    ];

    protected const SUGGESTIONS = [
        'keluarga' => 'Gali lebih jauh dinamika di rumah dan pertimbangkan sesi tatap muka.',
        'pertemanan' => 'Bantu murid memetakan teman yang bisa dipercaya dan latih cara berkomunikasi asertif.',
        'asmara' => 'Diskusikan batasan sehat dalam hubungan dan dukung murid mengambil keputusan sendiri.',
        'bullying' => 'Pastikan keamanan murid, catat kronologi, dan koordinasikan dengan wali kelas sesuai protokol.',
        'akademik' => 'Bantu menyusun jadwal belajar realistis dan pecah tugas menjadi langkah kecil.',
        'diri-sendiri' => 'Perkuat penerimaan diri dan ajak murid mengenali kelebihannya.',
        'masa-depan' => 'Tawarkan sesi eksplorasi minat-bakat dan informasi jurusan/karier.',
        'belum-tahu' => 'Ajak murid mengurai perasaannya satu per satu dengan pertanyaan terbuka.',
    ];

    public function name(): string
    {
        return 'fake';
    }

    public function summarize(array $messages, array $context): array
    {
        $studentTexts = collect($messages)->where('role', 'Murid')->pluck('text');
        $counselorTexts = collect($messages)->where('role', 'Guru')->pluck('text');

        $longest = $studentTexts->sortByDesc(fn ($t) => mb_strlen($t))->take(3)->values();
        $ringkasan = 'Murid bercerita bahwa '.lcfirst(Str::limit((string) $longest->first(), 160, '…'));
        if ($longest->count() > 1) {
            $ringkasan .= ' Ia juga menyampaikan: "'.Str::limit((string) $longest->get(1), 120, '…').'"';
        }
        $ringkasan .= ' Guru BK sudah merespons '.$counselorTexts->count().' kali.';

        $all = mb_strtolower($studentTexts->implode(' '));
        $emotions = collect(self::EMOTIONS)->filter(fn ($words) => collect($words)->contains(fn ($w) => str_contains($all, $w)))->keys()->values()->all();

        $severity = (int) ($context['max_flag_severity'] ?? 0);
        $level = match (true) {
            $severity >= 3 || ($context['urgency'] ?? '') === 'urgent' => 'tinggi',
            $severity === 2 || ($context['urgency'] ?? '') === 'important' => 'sedang',
            default => 'rendah',
        };
        $alasan = match ($level) {
            'tinggi' => $severity >= 3 ? 'Terdeteksi kata kunci sensitif tingkat kritis dalam percakapan.' : 'Murid memilih tingkat urgensi "Sangat butuh bantuan".',
            'sedang' => 'Ada sinyal yang perlu dipantau (kata kunci serius atau urgensi cukup penting).',
            default => 'Tidak ditemukan indikasi bahaya dari kata kunci yang dipantau.',
        };

        $topicSlugs = $context['topic_slugs'] ?? [];
        $saran = collect($topicSlugs)->map(fn ($s) => self::SUGGESTIONS[$s] ?? null)->filter()->values()->all();
        if ($level === 'tinggi') {
            array_unshift($saran, 'Prioritaskan keselamatan: pertimbangkan meminta murid membuka identitas atau mengarahkan ke bantuan darurat.');
        }
        if (! $saran) {
            $saran = ['Lanjutkan mendengarkan dan tanyakan apa yang murid butuhkan saat ini.'];
        }

        return SummaryValidator::validate([
            'ringkasan' => $ringkasan,
            'inti_masalah' => $longest->map(fn ($t) => Str::limit($t, 90, '…'))->all(),
            'emosi_terdeteksi' => $emotions,
            'tindakan_sudah_dilakukan' => $counselorTexts->take(-2)->map(fn ($t) => 'Guru: '.Str::limit($t, 80, '…'))->values()->all(),
            'saran_langkah_berikutnya' => $saran,
            'tingkat_risiko' => ['level' => $level, 'alasan' => $alasan],
        ]);
    }
}
