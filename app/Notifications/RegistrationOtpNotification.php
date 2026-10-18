<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Kode OTP untuk memverifikasi email saat murid mendaftar. Dikirim langsung (tanpa antrian) agar kode
 * tidak tertunda menunggu cron. Isi email netral: tidak menyebut BK, cerita, atau sekolah.
 */
class RegistrationOtpNotification extends Notification
{
    public function __construct(public string $code, public int $validMinutes) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("{$this->code} adalah kode verifikasi Welcoming-mu")
            ->greeting('Halo!')
            ->line('Masukkan kode berikut untuk menyelesaikan pendaftaran akun Welcoming-mu:')
            ->line("**{$this->code}**")
            ->line("Kode ini berlaku {$this->validMinutes} menit dan hanya bisa dipakai sekali. Jangan berikan kode ini kepada siapa pun.")
            ->line('Kalau kamu tidak merasa mendaftar, abaikan saja email ini. Tidak ada akun yang dibuat tanpa kode ini.');
    }
}
