<?php

namespace App\Services;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MoodEntry;
use App\Models\MoodPromptLog;
use App\Models\School;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class MoodService
{
    public const TINTS = ['happy' => '#E4EADF', 'neutral' => '#F3E6D0', 'sad' => '#EDE5D0', 'anxious' => '#F6DDD7', 'angry' => '#F2CBC1'];

    public const REPLIES = [
        'happy' => 'Asyik! Simpan momen ini di jurnalmu, ya.',
        'neutral' => 'Hari biasa juga berharga. Sudah tercatat di jurnalmu.',
        'sad' => 'Nggak apa-apa merasa sedih. Mau cerita ke Guru BK? Anonim kok.',
        'anxious' => 'Cemas itu berat. Coba latihan napas 1 menit di Self-Help.',
        'angry' => 'Marah itu wajar. Mau tulis dulu apa yang bikin kesal?',
    ];

    public function today(): CarbonImmutable
    {
        return CarbonImmutable::now(School::current()->timezone)->startOfDay();
    }

    public function todayEntry(User $student): ?MoodEntry
    {
        return MoodEntry::where('student_id', $student->id)->whereDate('entry_date', $this->today()->toDateString())->first();
    }

    /** @param  array<string, ?string>|null  $song  lagu yang menggambarkan hari ini (null = hapus) */
    public function checkin(User $student, string $mood, ?string $note = null, bool $touchNote = false, ?array $song = null, bool $touchSong = false): MoodEntry
    {
        $entry = MoodEntry::firstOrNew(['student_id' => $student->id, 'entry_date' => $this->today()->toDateString()]);
        $entry->mood = $mood;
        if ($touchNote) {
            $entry->note = filled($note) ? $note : null;
        }
        if ($touchSong) {
            $entry->song = $song;
        }
        $entry->save();

        return $entry;
    }

    /**
     * Pop-up ajakan cerita: semua kondisi terpenuhi (7.17).
     * 1) mood_prompt_enabled; 2) 3 entri terakhir di 3 hari kalender berturut-turut negatif;
     * 3) tidak ada log ajakan 7 hari terakhir; 4) tidak mengirim pesan di percakapan mana pun 3 hari terakhir.
     */
    public function shouldShowPrompt(User $student): bool
    {
        if (! ($student->studentProfile?->mood_prompt_enabled ?? false)) {
            return false;
        }
        $last = MoodEntry::where('student_id', $student->id)->orderByDesc('entry_date')->limit(3)->get(['entry_date', 'mood']);
        if ($last->count() < 3 || $last->contains(fn ($e) => ! $e->isNegative())) {
            return false;
        }
        $dates = $last->map(fn ($e) => CarbonImmutable::parse($e->entry_date->toDateString()))->values();
        if (! $dates[1]->addDay()->isSameDay($dates[0]) || ! $dates[2]->addDay()->isSameDay($dates[1])) {
            return false;
        }
        if (MoodPromptLog::where('student_id', $student->id)->where('shown_at', '>=', now()->subDays(7))->exists()) {
            return false;
        }
        $recentMessage = Message::whereIn('conversation_id', Conversation::forStudent($student)->select('id'))
            ->where('sender_type', 'student')->where('created_at', '>=', now()->subDays(3))->exists();

        return ! $recentMessage;
    }

    /** Tampilkan pop-up sekali (dicatat), kembalikan 3 entri terakhir untuk ditampilkan. */
    public function consumePrompt(User $student): ?Collection
    {
        if (! $this->shouldShowPrompt($student)) {
            return null;
        }
        MoodPromptLog::create(['student_id' => $student->id, 'shown_at' => now()]);

        return MoodEntry::where('student_id', $student->id)->orderByDesc('entry_date')->limit(3)->get()->reverse()->values();
    }

    /** @return Collection<int, array{date: CarbonImmutable, entry: ?MoodEntry}> */
    public function week(User $student): Collection
    {
        $today = $this->today();
        $entries = MoodEntry::where('student_id', $student->id)->where('entry_date', '>=', $today->subDays(6)->toDateString())->get()
            ->keyBy(fn ($e) => $e->entry_date->toDateString());

        return collect(range(6, 0))->map(fn ($i) => ['date' => $today->subDays($i), 'entry' => $entries->get($today->subDays($i)->toDateString())]);
    }

    /** Sel kalender bulanan (mulai Senin). */
    public function calendar(User $student, CarbonImmutable $month): array
    {
        $start = $month->startOfMonth();
        $entries = MoodEntry::where('student_id', $student->id)
            ->whereBetween('entry_date', [$start->toDateString(), $start->endOfMonth()->toDateString()])->get()
            ->keyBy(fn ($e) => (int) $e->entry_date->format('j'));
        $lead = ($start->dayOfWeekIso - 1);
        $cells = array_fill(0, $lead, null);
        for ($d = 1; $d <= $start->daysInMonth; $d++) {
            $cells[] = ['day' => $d, 'date' => $start->setDay($d), 'entry' => $entries->get($d)];
        }

        return ['cells' => $cells, 'entries' => $entries];
    }

    /** Data grafik 30 hari (skor hanya untuk visual tren). */
    public function trend(User $student): array
    {
        $today = $this->today();
        $entries = MoodEntry::where('student_id', $student->id)->where('entry_date', '>=', $today->subDays(29)->toDateString())->get()
            ->keyBy(fn ($e) => $e->entry_date->toDateString());
        $labels = [];
        $values = [];
        $moods = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = $today->subDays($i);
            $e = $entries->get($d->toDateString());
            $labels[] = $d->translatedFormat('j M');
            $values[] = $e ? MoodEntry::SCORES[$e->mood] : null;
            $moods[] = $e ? MoodEntry::LABELS[$e->mood] : null;
        }

        return compact('labels', 'values', 'moods');
    }
}
