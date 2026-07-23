<?php

use App\Models\Setting;
use App\Services\Pacing\ExamDateResolver;

/**
 * The exam date is resolved from the student's target SEA year: a sensible
 * early-April default, overridable by an admin-set official date per year
 * (reusing the key/value Setting store) once the MoE announces it.
 */
it('derives a default early-April exam date from the target year', function () {
    $date = app(ExamDateResolver::class)->resolve(2027);

    expect($date->format('Y-m-d'))->toBe('2027-04-01');
})->group('scenario:RR-06');

it('prefers an admin-set official exam date over the derived default', function () {
    Setting::put('sea_exam_date_2027', '2027-03-25');

    $date = app(ExamDateResolver::class)->resolve(2027);

    expect($date->format('Y-m-d'))->toBe('2027-03-25');
})->group('scenario:RR-06');
