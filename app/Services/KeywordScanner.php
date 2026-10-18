<?php

namespace App\Services;

use App\Models\SensitiveKeyword;
use Illuminate\Support\Collection;

class KeywordScanner
{
    /** @var array<int, list<array{0: SensitiveKeyword, 1: string}>> Kata kunci aktif + pola regex per sekolah, dimuat sekali per instance. */
    protected array $patterns = [];

    public function __construct(protected TextNormalizer $normalizer) {}

    /** @return Collection<int, SensitiveKeyword> Kata kunci aktif milik sekolah percakapan yang cocok dengan teks. */
    public function scan(string $text, int $schoolId): Collection
    {
        $normalized = $this->normalizer->normalize($text);
        if ($normalized === '') {
            return collect();
        }

        return collect($this->patternsFor($schoolId))
            ->filter(fn (array $entry) => (bool) preg_match($entry[1], $normalized))
            ->map(fn (array $entry) => $entry[0])
            ->values();
    }

    /** @return list<array{0: SensitiveKeyword, 1: string}> */
    protected function patternsFor(int $schoolId): array
    {
        return $this->patterns[$schoolId] ??= SensitiveKeyword::query()->where('school_id', $schoolId)->where('is_active', true)->get()
            ->map(fn (SensitiveKeyword $kw) => [$kw, $this->normalizer->normalize($kw->phrase)])
            ->filter(fn (array $entry) => $entry[1] !== '')
            ->map(fn (array $entry) => [$entry[0], '/(?<![\p{L}\p{N}])'.str_replace('\ ', '\s+', preg_quote($entry[1], '/')).'(?![\p{L}\p{N}])/u'])
            ->values()->all();
    }
}
