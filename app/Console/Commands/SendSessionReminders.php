<?php

namespace App\Console\Commands;

use App\Models\CounselingSession;
use App\Models\School;
use App\Models\User;
use App\Support\Notify;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendSessionReminders extends Command
{
    protected $signature = 'welcoming:session-reminders';

    protected $description = 'Kirim pengingat H-1 untuk sesi tatap muka besok';

    /** "Besok" dihitung per sekolah memakai zona waktu sekolah guru yang menangani sesi. */
    public function handle(): int
    {
        $sent = 0;
        foreach (School::all() as $school) {
            $tomorrow = CarbonImmutable::now($school->timezone)->addDay();
            $sessions = CounselingSession::with(['slot', 'student.studentProfile'])->where('status', 'scheduled')
                ->whereIn('counselor_id', User::counselors()->inSchool($school)->select('id'))
                ->whereHas('slot', fn ($q) => $q->whereBetween('start_at', [$tomorrow->startOfDay()->utc(), $tomorrow->endOfDay()->utc()]))->get();
            foreach ($sessions as $s) {
                Notify::user($s->student, 'session_reminder', 'Pengingat: besok ada jadwal untukmu', route('student.schedule.index'));
            }
            $sent += $sessions->count();
        }
        $this->info($sent.' pengingat terkirim.');

        return self::SUCCESS;
    }
}
