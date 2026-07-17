<?php

use App\Livewire\GuardianProgress;
use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function gpSeedGuardianWithProgress(): array
{
    $guardian = User::factory()->create(['role' => 'guardian']);
    $student  = User::factory()->create([
        'role'      => 'student',
        'parent_id' => $guardian->id,
    ]);

    StudentJourney::create([
        'student_id'    => $student->id,
        'journey_start' => Carbon::today()->subWeeks(4)->toDateString(),
        'exam_date'     => Carbon::parse('2026-05-21')->toDateString(),
    ]);

    // One Math module per status; one ELA inferred. Factory-create is safe in :memory:.
    $mMastered = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 1, 'topic' => 'Number: Place Value to Millions']);
    $mInferred = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 2, 'topic' => 'Number: Rounding Whole Numbers']);
    $mNeeds    = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 3, 'topic' => 'Number: Factors and Multiples']);
    $mUpcoming = SyllabusModule::factory()->create(['subject' => 'Math', 'pacing_week' => 4, 'topic' => 'Number: Prime Numbers']);
    $eInferred = SyllabusModule::factory()->create(['subject' => 'ELA',  'pacing_week' => 1, 'topic' => 'Grammar: Subject Verb Agreement']);

    StudentProgress::create(['student_id' => $student->id, 'module_id' => $mMastered->id, 'status' => 'mastered']);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $mInferred->id, 'status' => 'inferred_mastered']);
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $mNeeds->id,    'status' => 'needs_work']);
    // $mUpcoming: no progress row → upcoming (not_started)
    StudentProgress::create(['student_id' => $student->id, 'module_id' => $eInferred->id, 'status' => 'inferred_mastered']);

    return compact('guardian', 'student', 'mMastered', 'mInferred', 'mNeeds', 'mUpcoming', 'eInferred');
}

it('groups a subject\'s modules into the four honest buckets', function () {
    $c = gpSeedGuardianWithProgress();

    Livewire::actingAs($c['guardian'])
        ->test(GuardianProgress::class)
        ->assertViewHas('buckets', function ($buckets) use ($c) {
            $math = $buckets['Math'] ?? null;
            if (! $math) return false;

            return in_array($c['mMastered']->id, collect($math['mastered'])->pluck('id')->all(), true)
                && in_array($c['mInferred']->id, collect($math['in_review'])->pluck('id')->all(), true)
                && in_array($c['mNeeds']->id,    collect($math['working_on'])->pluck('id')->all(), true)
                && in_array($c['mUpcoming']->id, collect($math['upcoming'])->pluck('id')->all(), true);
        });
})->group('scenario:GD-02');

it('places inferred mastery in review and never in mastered', function () {
    $c = gpSeedGuardianWithProgress();

    Livewire::actingAs($c['guardian'])
        ->test(GuardianProgress::class)
        ->assertViewHas('buckets', function ($buckets) use ($c) {
            $masteredIds = collect($buckets['Math']['mastered'])->pluck('id')->all();
            $inReviewIds = collect($buckets['Math']['in_review'])->pluck('id')->all();

            // inferred module is in review, and NOT among mastered
            return in_array($c['mInferred']->id, $inReviewIds, true)
                && ! in_array($c['mInferred']->id, $masteredIds, true);
        });
})->group('scenario:GD-02');

it('shows the four bucket labels and the writing awaiting-assessment line', function () {
    $c = gpSeedGuardianWithProgress();

    Livewire::actingAs($c['guardian'])
        ->test(GuardianProgress::class)
        ->assertSeeText('Mastered')
        ->assertSeeText('In review')
        ->assertSeeText('Working on')
        ->assertSeeText('Upcoming')
        ->assertSeeText('Writing')
        ->assertSeeText('awaiting its own assessment track');
})->group('scenario:GD-02');

it('is reachable at the guardian progress route', function () {
    $c = gpSeedGuardianWithProgress();

    $this->actingAs($c['guardian'])
        ->get('/guardian/progress')
        ->assertOk();
})->group('scenario:GD-02');
