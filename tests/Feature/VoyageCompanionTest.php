<?php

use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Motivation\StreakService;
use App\Support\VoyageCompanion;
use Database\Seeders\ModulePrerequisiteSeeder;
use Database\Seeders\SyllabusModuleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

function vcStudent(): User
{
    return User::create([
        'name' => 'Aaliyah',
        'email' => 'vc-'.uniqid().'@students.formynieces.com',
        'password' => bcrypt('secret'),
        'role' => 'student',
        'onboarding_completed_at' => now(),
    ]);
}

/**
 * VC-01..03 — the Voyage companion: a warm, deterministic voice on her home
 * screen, composed only from data already on the Voyage (her name, her streak,
 * this week's topics). It never invents progress and never speaks the guardian's
 * gauge — no pace, no percentage.
 */
it('greets a returning student by name and names her live streak warmly', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = vcStudent();
    app(StreakService::class)->recordActivity($student->id, 'practice', Carbon::yesterday());
    app(StreakService::class)->recordActivity($student->id, 'practice', Carbon::today());

    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee('vy-companion')
        ->assertSee("Welcome back, {$student->name}")
        ->assertSee('in a row')            // the companion's warm streak line
        ->assertSee('smooth-cheer.webp');  // Smooth cheers her streak
})->group('scenario:VC-01');

it('names this week\'s plan by topic on the Voyage, never by count or pace', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = vcStudent();
    $module = SyllabusModule::orderBy('sequence_order')->first();

    WeeklyTarget::create([
        'student_id' => $student->id,
        'module_id' => $module->id,
        'week_start_date' => Carbon::today()->startOfWeek()->toDateString(),
        'is_completed' => false,
    ]);

    // "Number Concepts: Place Value up to One Million" -> child-kind short form.
    $shortTopic = trim(Str::after($module->topic, ': '));

    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee('vy-companion')
        ->assertSee('This week')
        ->assertSee($shortTopic)           // named by topic (module topics appear nowhere else on the overworld)
        ->assertSee('smooth-chart.webp');  // Smooth points at the chart for the weekly plan
})->group('scenario:VC-02');

it('invents no streak or plan for a student who has neither', function () {
    $this->seed(SyllabusModuleSeeder::class);
    $this->seed(ModulePrerequisiteSeeder::class);

    $student = vcStudent();   // no streak recorded, no weekly target

    $this->actingAs($student)->get(route('student.voyage'))
        ->assertOk()
        ->assertSee('vy-companion')
        ->assertSee($student->name)
        ->assertDontSee('in a row')        // no streak line invented
        ->assertDontSee('This week')       // no plan line invented
        ->assertSee('smooth.webp');        // Smooth simply waves hello (the calm default)
})->group('scenario:VC-03');

it('composes greeting by name, a streak line only when active, and a plan only when topics exist', function () {
    $withAll = VoyageCompanion::for('Maya', ['practice' => 7], ['Number Concepts: Rounding to the Nearest Thousand']);
    expect($withAll['greeting'])->toContain('Maya')
        ->and($withAll['streak'])->toContain('in a row')
        ->and($withAll['plan'])->toContain('Rounding to the Nearest Thousand')
        ->and($withAll['plan'])->not->toContain('Number Concepts')
        ->and($withAll['avatar'])->toBe('cheer');   // streak takes priority

    $planOnly = VoyageCompanion::for('Maya', ['practice' => 0], ['Fractions: Equivalent Fractions']);
    expect($planOnly['avatar'])->toBe('chart');     // a plan, no streak -> points at the chart

    $empty = VoyageCompanion::for('Maya', ['practice' => 0, 'login' => 0, 'mastery' => 0], []);
    expect($empty['greeting'])->toContain('Maya')
        ->and($empty['streak'])->toBeNull()
        ->and($empty['plan'])->toBeNull()
        ->and($empty['avatar'])->toBe('wave');       // nothing to react to -> waves hello

    // The companion never leaks the guardian's gauge.
    foreach (array_filter([$withAll['greeting'], $withAll['streak'], $withAll['plan']]) as $line) {
        expect($line)->not->toContain('%')
            ->and(strtolower($line))->not->toContain('pace')
            ->and(strtolower($line))->not->toContain('percent');
    }
});
