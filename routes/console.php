<?php

use App\Models\School;
use Illuminate\Support\Facades\Schedule;

$tz = rescue(fn () => School::current()->timezone, 'Asia/Makassar', false);

Schedule::command('welcoming:mark-offline')->everyMinute()->withoutOverlapping();
Schedule::command('welcoming:recalculate-priorities')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('welcoming:expire-invitations')->hourly();
Schedule::command('welcoming:send-follow-ups')->dailyAt('07:00')->timezone($tz);
Schedule::command('welcoming:session-reminders')->dailyAt('18:00')->timezone($tz);
Schedule::command('welcoming:purge-voice-notes')->dailyAt('02:00')->timezone($tz);
