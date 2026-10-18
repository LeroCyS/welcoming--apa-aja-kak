<?php

namespace App\Services\Ai;

use App\Contracts\Transcriber;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiTranscriber implements Transcriber
{
    public function transcribe(string $path): string
    {
        $cfg = config('welcoming.ai');
        if (blank($cfg['api_key']) || blank($cfg['transcribe_model'])) {
            throw new RuntimeException('AI_API_KEY atau AI_TRANSCRIBE_MODEL belum diatur.');
        }

        $response = Http::timeout(60)->retry(2, 1000)->withToken($cfg['api_key'])
            ->attach('file', fopen($path, 'r'), basename($path))
            ->post(rtrim($cfg['base_url'] ?: 'https://api.openai.com/v1', '/').'/audio/transcriptions', [
                'model' => $cfg['transcribe_model'],
                'language' => 'id',
            ])->throw();

        return trim((string) $response->json('text'));
    }

    public function isFake(): bool
    {
        return false;
    }
}
