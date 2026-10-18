<?php

namespace App\Notifications;

use App\Models\User;
use App\Notifications\Channels\SafeBroadcastChannel;
use App\Support\NotificationKind;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

/**
 * Notifikasi umum Welcoming. Dikirim langsung (tanpa antrian) supaya tetap muncul walau `queue:work` tidak berjalan.
 * Teks untuk murid selalu netral (tanpa kata "BK"/"konseling"). Jika murid mematikan notifikasi,
 * notifikasi tetap tersimpan di database tanpa broadcast maupun push ke perangkat.
 */
class WelcomingNotification extends Notification
{
    public function __construct(
        public string $kind,
        public string $title,
        public string $url,
        public bool $critical = false,
        public ?string $body = null,
        public ?int $messageId = null,
    ) {}

    public function via(User $notifiable): array
    {
        $channels = ['database'];
        if ($notifiable->isStudent() && ! ($notifiable->studentProfile?->notifications_enabled ?? true)) {
            return $channels;
        }
        $channels[] = SafeBroadcastChannel::class;
        if ($notifiable->pushSubscriptions()->exists()) {
            $channels[] = WebPushChannel::class;
        }

        return $channels;
    }

    public function toArray(User $notifiable): array
    {
        return array_filter([
            'kind' => $this->kind,
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'critical' => $this->critical,
            // Dipakai untuk memperbarui potongan isi saat pesannya diedit / dihapus.
            'message_id' => $this->messageId,
        ], fn ($v) => $v !== null);
    }

    public function toBroadcast(User $notifiable): BroadcastMessage
    {
        return new BroadcastMessage(['kind' => $this->kind, 'critical' => $this->critical]);
    }

    /**
     * Notifikasi perangkat (Web Push). Untuk murid isinya selalu netral karena bisa terlihat di layar kunci HP:
     * tanpa nama guru, topik, maupun potongan pesan.
     */
    public function toWebPush(User $notifiable, Notification $notification): WebPushMessage
    {
        [$title, $body] = $notifiable->isStudent()
            ? ['Welcoming', 'Ada kabar baru untukmu.']
            : [$this->title, $this->body ?? NotificationKind::for($this->kind)['label']];

        return (new WebPushMessage)
            ->title($title)
            ->body($body)
            ->icon(asset('images/welcoming-mark.png'))
            ->badge(asset('images/welcoming-mark.png'))
            ->tag($notifiable->isStudent() ? 'welcoming' : $this->kind.':'.md5($this->url))
            ->renotify()
            ->requireInteraction($this->critical)
            ->data(['url' => $this->url])
            ->options(['TTL' => 3600, 'urgency' => $this->critical ? 'high' : 'normal']);
    }

    public function databaseType(User $notifiable): string
    {
        return $this->kind;
    }
}
