<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// RR-10: sweep for guardian reconciliations left unanswered for three days and
// auto-proceed them so a student's progress is never halted by an absent guardian.
Schedule::command('reconciliation:auto-proceed')->daily();

// QB-12/13: daily snapshot of the practice question bank, pruning backups older
// than 30 days so the last month is always restorable.
Schedule::command('questions:backup')->dailyAt('02:00');

// LG-08: fall lapsed funnel trials back to the free plan, daily.
Schedule::command('trials:expire')->dailyAt('03:00');

// LG-10: the weekly "SEA Question of the Week" nurture email to opted-in leads.
Schedule::command('funnel:weekly-question')->weeklyOn(1, '08:00');

// LL-17: the weekly review that slips a mastered level to "mastered_review" when
// its maintenance window + grace passed without a re-mastery, making it eligible
// for a future weekly target again.
Schedule::command('practice:decay-maintenance')->weekly();

// WT-03: the once-a-week recalculation of every active student's pace and
// progress. Runs Sunday at 01:00 — the start of the pacing week — so the
// guardian dashboard opens Monday on a freshly recalculated, stably-dated report.
Schedule::command('pace:weekly-recalculation')->weeklyOn(0, '01:00');
