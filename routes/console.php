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
