<?php

namespace App\Notifications\Channels;

use Illuminate\Contracts\Broadcasting\Factory as BroadcastFactory;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Broadcast notifikasi secara langsung dan toleran: bila Reverb tidak berjalan, notifikasi database
 * tetap tersimpan dan klien memakai polling, tanpa menumpuk failed jobs.
 */
class SafeBroadcastChannel extends BroadcastChannel
{
    public function send($notifiable, Notification $notification): void
    {
        $message = $this->getData($notifiable, $notification);
        $event = new BroadcastNotificationCreated($notifiable, $notification, is_array($message) ? $message : $message->data);

        try {
            app(BroadcastFactory::class)->connection()->broadcast($event->broadcastOn(), $event->broadcastAs(), $event->broadcastWith());
        } catch (Throwable $e) {
            Log::debug('Broadcast notifikasi dilewati: '.$e->getMessage());
        }
    }
}
