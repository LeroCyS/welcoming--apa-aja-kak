<?php

namespace App\Contracts;

interface Summarizer
{
    /**
     * @param  list<array{role: string, text: string}>  $messages  pesan yang sudah disamarkan
     * @param  array{topics?: list<string>, urgency?: string, max_flag_severity?: int}  $context
     * @return array{ringkasan: string, inti_masalah: list<string>, emosi_terdeteksi: list<string>, tindakan_sudah_dilakukan: list<string>, saran_langkah_berikutnya: list<string>, tingkat_risiko: array{level: string, alasan: string}}
     */
    public function summarize(array $messages, array $context): array;

    public function name(): string;
}
