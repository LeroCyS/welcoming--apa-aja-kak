<?php

namespace App\Services\Ai;

use App\Contracts\Summarizer;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicSummarizer implements Summarizer
{
    public function name(): string
    {
        return 'anthropic';
    }

    public function summarize(array $messages, array $context): array
    {
        $cfg = config('welcoming.ai');
        if (blank($cfg['api_key']) || blank($cfg['model'])) {
            throw new RuntimeException('AI_API_KEY atau AI_MODEL belum diatur.');
        }

        $response = Http::timeout(60)->retry(2, 1000)
            ->withHeaders(['x-api-key' => $cfg['api_key'], 'anthropic-version' => '2023-06-01'])
            ->post(rtrim($cfg['base_url'] ?: 'https://api.anthropic.com', '/').'/v1/messages', [
                'model' => $cfg['model'],
                'max_tokens' => 1200,
                'system' => SummaryValidator::systemPrompt(),
                'messages' => [['role' => 'user', 'content' => SummaryValidator::userPrompt($messages, $context)]],
            ])->throw();

        $text = collect($response->json('content', []))->where('type', 'text')->pluck('text')->implode("\n");

        return SummaryValidator::validate(SummaryValidator::extractJson($text));
    }
}
