<?php

use App\Models\StudentJourney;
use App\Models\StudentProgress;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\LlmService;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->mock(LlmService::class, fn ($m) => $m->shouldReceive('completeJson')->andReturn([]));
    Carbon::setTestNow(Carbon::parse('2026-08-18 09:00'));
});

function tbStudent(string $stage): User
{
    $s = User::create([
        'name' => 'Amara', 'email' => 'tb-'.uniqid().'@s.local', 'password' => bcrypt('x'),
        'role' => 'student', 'onboarding_completed_at' => now(), 'welcomed_at' => now(),
        'tour_stage' => $stage, 'reading_level' => 5,
    ]);
    StudentJourney::create(['student_id' => $s->id, 'journey_start' => Carbon::today()->subWeeks(2)->toDateString(), 'exam_date' => '2026-05-21']);

    return $s;
}

it('welcome page shows the new perk copy', function () {
    $s = User::create(['name' => 'Amara', 'email' => 'w-'.uniqid().'@s.local', 'password' => bcrypt('x'), 'role' => 'student', 'onboarding_completed_at' => now()]);
    $this->actingAs($s);
    $page = visit('/welcome');
    $page->assertNoJavascriptErrors()
        ->assertSee('Freeze all streaks for one day')
        ->assertSee('Did a streak reset?');
});

it('the overworld tour auto-opens for a student on tour', function () {
    // The tour's chapter walk is owned by the tour feature itself; here we only
    // assert it opens on the overworld and offers a way to step/skip.
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);
    $s = tbStudent('overworld');
    $module = SyllabusModule::orderBy('sequence_order')->first();
    WeeklyTarget::create(['student_id' => $s->id, 'module_id' => $module->id, 'week_start_date' => Carbon::today()->startOfWeek()->toDateString(), 'is_completed' => false]);
    StudentProgress::firstOrCreate(['student_id' => $s->id, 'module_id' => $module->id], ['status' => 'needs_work']);
    $this->actingAs($s);

    $page = visit('/voyage');
    $page->assertNoJavascriptErrors()
        ->assertSee('your turtle first mate')   // the tour opened on chapter one
        ->assertSee('Skip the tour');           // and offers a way out
});

it('captains orders shows the paced task list', function () {
    $s = tbStudent('done');
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'pacing_week' => 1]);
    WeeklyTarget::create(['student_id' => $s->id, 'module_id' => $module->id, 'week_start_date' => Carbon::today()->startOfWeek()->toDateString(), 'is_completed' => false]);
    StudentProgress::create(['student_id' => $s->id, 'module_id' => $module->id, 'status' => 'needs_work']);
    $this->actingAs($s);

    $page = visit('/voyage');
    $page->assertNoJavascriptErrors()
        ->assertSee('finish these to stay on course')
        ->assertSee('Fractions: Adding');
});

it('lesson tour explains the loop on a module page', function () {
    $s = tbStudent('island');
    $module = SyllabusModule::factory()->create(['subject' => 'Math', 'topic' => 'Fractions: Adding', 'pacing_week' => 1]);
    StudentProgress::create(['student_id' => $s->id, 'module_id' => $module->id, 'status' => 'needs_work']);
    $this->actingAs($s);

    $page = visit("/practice/{$module->id}/enter");
    $page->assertNoJavascriptErrors()
        ->assertSee('This is a real stop');   // the lesson-loop tour is showing
});
