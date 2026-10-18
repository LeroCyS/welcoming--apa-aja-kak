<?php

namespace App\Contracts;

interface Transcriber
{
    /** Mengembalikan transkrip; melempar exception jika gagal. */
    public function transcribe(string $path): string;

    /** true jika driver tidak benar-benar mentranskripsi (mode demo). */
    public function isFake(): bool;
}
