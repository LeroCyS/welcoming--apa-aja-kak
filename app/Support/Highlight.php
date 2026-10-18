<?php

namespace App\Support;

class Highlight
{
    /** Tandai frasa (sudah di-escape) dengan <mark>. Pencocokan longgar, tidak mengubah teks asli. */
    public static function phrases(string $escapedHtml, array $phrases): string
    {
        foreach (array_filter(array_unique($phrases)) as $p) {
            $pattern = '/('.str_replace('\ ', '[\s\p{P}]+', preg_quote(e($p), '/')).')/iu';
            $escapedHtml = preg_replace($pattern, '<mark class="bg-sand-tint text-inherit px-0.5 rounded">$1</mark>', $escapedHtml) ?? $escapedHtml;
        }

        return $escapedHtml;
    }
}
