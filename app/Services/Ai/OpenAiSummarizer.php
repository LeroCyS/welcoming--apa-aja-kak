<?php

namespace App\Services\Ai;

use App\Contracts\Summarizer;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiSummarizer implements Summarizer
{
    public function name(): string
    {
        return 'openai';
    }

    public function summarize(array $messages, array $context): array
    {
        $cfg = config('welcoming.ai');
        if (blank($cfg['api_key']) || blank($cfg['model'])) {
            throw new RuntimeException('AI_API_KEY atau AI_MODEL belum diatur.');
        }

        $response = Http::timeout(60)->retry(2, 1000)->withToken($cfg['api_key'])
            ->post(rtrim($cfg['base_url'] ?: 'https://api.openai.com/v1', '/').'/chat/completions', [
                'model' => $cfg['model'],
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => SummaryValidator::systemPrompt()],
                    ['role' => 'user', 'content' => SummaryValidator::userPrompt($messages, $context)],
                ],
            ])->throw();

        return SummaryValidator::validate(SummaryValidator::extractJson((string) $response->json('choices.0.message.content')));
    }
}
