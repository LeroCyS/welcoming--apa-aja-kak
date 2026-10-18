<?php

namespace App\Support;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\WelcomingNotification;
use App\Services\StudentResolver;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class Notify
{
    public static function user(?User $user, string $kind, string $title, string $url, bool $critical = false, ?string $body = null, ?int $messageId = null): void
    {
        if (! $user) {
            return;
        }
        try {
            $user->notify(new WelcomingNotification($kind, $title, $url, $critical, $body, $messageId));
        } catch (Throwable $e) {
            Log::warning('Notifikasi gagal: '.$e->getMessage());
        }
    }

    /** Notifikasi ke guru yang menangani percakapan. */
    public static function counselor(Conversation $c, string $kind, string $title, bool $critical = false, ?string $body = null): void
    {
        static::user($c->counselor, $kind, $title, route('counselor.conversations.show', $c), $critical, $body);
    }

    /** Notifikasi ke murid pemilik percakapan (teks wajib netral). */
    public static function student(Conversation $c, string $kind, string $title, ?string $url = null, ?string $body = null): void
    {
        static::user(app(StudentResolver::class)->resolve($c), $kind, $title, $url ?? route('student.conversations.show', $c), false, $body);
    }

    /** Notifikasi yang mengarah ke percakapan ini dianggap terbaca begitu pesannya dibaca. */
    public static function markConversationRead(User $user, Conversation $c): void
    {
        $url = $user->isCounselor() ? route('counselor.conversations.show', $c) : route('student.conversations.show', $c);
        $user->unreadNotifications()->where('data->url', $url)->update(['read_at' => now()]);
    }

    /** Potongan isi pesan untuk notifikasi di dalam aplikasi. */
    public static function preview(Message $m): string
    {
        return $m->preview(90);
    }

    /**
     * Pesan diedit / dihapus untuk semua orang: potongan isinya di notifikasi ikut diperbarui, supaya teks yang
     * sudah dihapus tidak tetap terbaca lewat notifikasi. Notifikasi lama (tanpa message_id) dicocokkan lewat
     * alamat percakapan + potongan teks sebelumnya.
     */
    public static function syncMessagePreview(Message $m, string $previousPreview): void
    {
        $c = $m->conversation;
        $urls = [route('counselor.conversations.show', $c), route('student.conversations.show', $c)];
        $preview = static::preview($m);

        DatabaseNotification::query()
            ->where(fn ($q) => $q->where('data->message_id', $m->id)
                ->orWhere(fn ($old) => $old->whereNull('data->message_id')->whereIn('data->url', $urls)->where('data->body', $previousPreview)))
            ->get()
            ->each(function (DatabaseNotification $n) use ($preview) {
                $n->forceFill(['data' => array_merge($n->data, ['body' => $preview])])->save();
            });
    }
}
