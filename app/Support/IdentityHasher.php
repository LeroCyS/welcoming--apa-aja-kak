<?php

namespace App\Support;

use RuntimeException;

class IdentityHasher
{
    public static function hash(int|string $userId): string
    {
        return hash_hmac('sha256', (string) $userId, static::key());
    }

    protected static function key(): string
    {
        $key = config('welcoming.identity_hash_key');

        if (blank($key)) {
            if (app()->environment('testing')) {
                return (string) config('app.key');
            }
            throw new RuntimeException('IDENTITY_HASH_KEY belum diisi di .env.');
        }

        return $key;
    }
}
