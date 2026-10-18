<?php

namespace App\Support;

use App\Events\RealtimeEvent;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Broadcast yang toleran: bila Reverb tidak berjalan, aplikasi tetap jalan (klien memakai polling). */
class Realtime
{
    public static function send(RealtimeEvent $event): void
    {
        try {
            broadcast($event);
        } catch (Throwable $e) {
            Log::debug('Realtime broadcast dilewati: '.$e->getMessage());
        }
    }

    public static function conversation(string $uuid, string $type, array $data = []): void
    {
        $class = 'App\\Events\\'.$type;
        static::send(new $class('conversation.'.$uuid, $data));
    }

    public static function counselor(int $id, string $type, array $data = []): void
    {
        $class = 'App\\Events\\'.$type;
        static::send(new $class('counselor.'.$id, $data));
    }
}
