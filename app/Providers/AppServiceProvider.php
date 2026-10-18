<?php

namespace App\Providers;

use App\Contracts\Summarizer;
use App\Contracts\Transcriber;
use App\Services\Ai\AnthropicSummarizer;
use App\Services\Ai\FakeSummarizer;
use App\Services\Ai\FakeTranscriber;
use App\Services\Ai\OpenAiSummarizer;
use App\Services\Ai\OpenAiTranscriber;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(Summarizer::class, fn () => match (config('welcoming.ai.summary_driver')) {
            'anthropic' => new AnthropicSummarizer,
            'openai' => new OpenAiSummarizer,
            default => new FakeSummarizer,
        });

        $this->app->bind(Transcriber::class, fn () => match (config('welcoming.ai.transcribe_driver')) {
            'openai' => new OpenAiTranscriber,
            default => new FakeTranscriber,
        });
    }

    public function boot(): void
    {
        Carbon::setLocale('id');

        ResetPassword::toMailUsing(fn ($user, string $token) => (new MailMessage)
            ->subject('Buat password baru - Welcoming')
            ->greeting('Halo!')
            ->line('Ada permintaan untuk membuat password baru akun Welcoming-mu.')
            ->action('Buat password baru', route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]))
            ->line('Tautan ini berlaku '.config('auth.passwords.users.expire').' menit. Kalau kamu tidak memintanya, abaikan saja email ini.'));

        RateLimiter::for('login', fn (Request $r) => Limit::perMinute(5)->by(mb_strtolower((string) $r->input('identifier')).'|'.$r->ip())
            ->response(fn () => back()->withInput($r->except('password'))->withErrors(['identifier' => 'Terlalu banyak percobaan masuk. Coba lagi dalam satu menit ya.'])));
        RateLimiter::for('register', fn (Request $r) => Limit::perHour(10)->by('register:'.$r->ip())
            ->response(fn () => back()->withInput($r->except('password', 'password_confirmation'))->withErrors(['email' => 'Terlalu banyak pendaftaran dari perangkat ini. Coba lagi nanti ya.'])));
        RateLimiter::for('register-otp', fn (Request $r) => Limit::perMinute(10)->by('register-otp:'.$r->ip())
            ->response(fn () => back()->withErrors(['code' => 'Terlalu banyak percobaan. Coba lagi dalam satu menit ya.'])));
        RateLimiter::for('password-reset', fn (Request $r) => Limit::perMinute(3)->by('pw-reset:'.$r->ip())
            ->response(fn () => back()->withInput()->withErrors(['email' => 'Terlalu banyak permintaan. Coba lagi dalam satu menit ya.'])));
        RateLimiter::for('messages', fn (Request $r) => Limit::perMinute(30)->by('msg:'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('voice', fn (Request $r) => Limit::perMinutes(10, 10)->by('voice:'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('summaries', fn (Request $r) => Limit::perHour(10)->by('sum:'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('pin', fn (Request $r) => Limit::perMinute(10)->by('pin:'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('pin-forgot', fn (Request $r) => Limit::perMinute(5)->by('pin-forgot:'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('media', fn (Request $r) => Limit::perMinutes(10, 20)->by('media:'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('typing', fn (Request $r) => Limit::perMinute(40)->by('typing:'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('stories', fn (Request $r) => Limit::perMinute(3)->by('stories:'.($r->user()?->id ?? $r->ip())));
        RateLimiter::for('reactions', fn (Request $r) => Limit::perMinute(60)->by('reactions:'.($r->user()?->id ?? $r->ip())));
    }
}
