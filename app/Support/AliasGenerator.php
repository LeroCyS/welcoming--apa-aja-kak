<?php

namespace App\Support;

use Illuminate\Support\Str;

class AliasGenerator
{
    /** @return array{alias: string, avatar: string} */
    public static function make(): array
    {
        $animal = collect(config('welcoming.alias_animals'))->random();
        $color = collect(config('welcoming.alias_colors'))->random();
        $number = random_int(100, 999);

        return [
            'alias' => "{$animal} {$color} #{$number}",
            'avatar' => Str::slug($animal).'--'.Str::slug($color),
        ];
    }

    /** Warna latar avatar anonim dari key. */
    public static function colorFor(string $avatar): array
    {
        $color = Str::after($avatar, '--');

        return match ($color) {
            'oranye' => ['#F6DDD7', '#8E3E2F'],
            'biru' => ['#DCE6EA', '#2F4A55'],
            'hijau' => ['#E4EADF', '#2F3D28'],
            'ungu' => ['#E7E0EC', '#4A3A57'],
            'kuning' => ['#F3E6D0', '#6F5229'],
            'merah-muda' => ['#FBEDE9', '#8E3E2F'],
            'toska' => ['#DDEBE6', '#2C4E45'],
            default => ['#EDE5D0', '#5A4220'],
        };
    }

    public static function initialFor(string $avatar): string
    {
        return mb_strtoupper(mb_substr(Str::before($avatar, '--'), 0, 1));
    }
}
