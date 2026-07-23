<?php

use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;

/**
 * Regression guard for the Laravel 13 date-cast trap: the student map queried
 * `week_start_date` with a Carbon (now()->startOfWeek()) against a date:Y-m-d
 * column, so a real current-week target never matched and "This Week's Target"
 * showed empty. Latent until RR-06 activated pacing for real students.
 */
it('shows the current weeks target on the student map', function () {
    $student = User::create([
        'name' => 'Aaliyah',
        'email' => 'wt-display-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);

    // Module has a weekly target but NO student_progress row, so its name can
    // only surface via the "This Week's Target" section, not the roadmap list.
    $module = SyllabusModule::create([
        'subject' => 'Math',
        'topic' => 'Fractions: Adding Zog Denominators',
        'sea_section' => 'Section I',
        'sequence_order' => 1,
    ]);

    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'week_start_date' => now()->startOfWeek()->toDateString(),
        'is_completed' => false,
    ]);

    $this->actingAs($student)->get(route('student.map'))
        ->assertOk()
        ->assertSeeText('Zog Denominators')
        ->assertDontSeeText('No target set for this week yet')
        ->assertSeeText('Not Started'); // no practice yet -> not-started chip (not the old misleading "In Progress")
});
