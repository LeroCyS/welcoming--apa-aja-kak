<?php

namespace App\Services;

class TextNormalizer
{
    /** @var array<string,string>|null */
    protected ?array $slang = null;

    public function normalize(string $text): string
    {
        $text = mb_strtolower($text);
        // hapus tanda baca → spasi
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);
        // huruf berulang ≥3 diciutkan jadi 1 ("capekkk" → "capek")
        $text = preg_replace('/(\p{L})\1{2,}/u', '$1', $text);
        $words = preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $map = $this->slangMap();
        $words = array_map(fn ($w) => $map[$w] ?? $w, $words);

        return implode(' ', $words);
    }

    /** @return array<string,string> */
    protected function slangMap(): array
    {
        if ($this->slang === null) {
            $this->slang = [];
            foreach (config('welcoming.slang_map', []) as $canonical => $variants) {
                foreach ($variants as $v) {
                    $this->slang[$v] = $canonical;
                }
            }
        }

        return $this->slang;
    }
}
