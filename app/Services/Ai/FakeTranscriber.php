<?php

namespace App\Services\Ai;

use App\Contracts\Transcriber;

class FakeTranscriber implements Transcriber
{
    public function transcribe(string $path): string
    {
        return '(Transkrip tidak tersedia dalam mode demo)';
    }

    public function isFake(): bool
    {
        return true;
    }
}
