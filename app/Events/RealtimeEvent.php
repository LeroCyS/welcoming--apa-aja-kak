<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event realtime berupa "ping" kecil. Payload TIDAK pernah memuat student_ref/hash/id atau nama;
 * klien mengambil data lengkap melalui endpoint yang sudah diotorisasi (sekaligus fallback polling).
 */
abstract class RealtimeEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /** @param array<string, scalar|null> $data */
    public function __construct(public string $channelName, public array $data = []) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel($this->channelName)];
    }

    public function broadcastAs(): string
    {
        return class_basename(static::class);
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
