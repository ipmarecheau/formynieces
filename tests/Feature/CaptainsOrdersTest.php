<?php

use App\Livewire\CaptainsOrders;
use App\Models\DailyPlan;
use App\Models\StudentProgress;
use App\Models\StudentStreak;
use App\Models\SyllabusModule;
use App\Models\User;
use App\Models\WeeklyTarget;
use App\Services\Motivation\StreakEconomyService;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

function coStudent(): User
{
    return User::factory()->create(['role' => 'student']);
}

it('renders a collapsible Captain\'s Orders sidebar', function () {
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->assertSet('collapsed', false)
        ->assertSet('tab', 'orders')
        ->assertSee('Captain')   // "Captain's Orders" (apostrophe renders as entity)
        ->assertSee('Orders')
        ->call('toggle')
        ->assertSet('collapsed', true)
        ->call('toggle')
        ->assertSet('collapsed', false);
})->group('scenario:CO-01');

it('shows an evening review in the evening brief', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-18 19:00')); // Tuesday evening
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->assertSee('Evening watch')
        ->assertSee('Look back on today');

    Carbon::setTestNow();
})->group('scenario:CO-06');

it('never shows the child a pace or deficit number', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-18 09:00'));
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->assertDontSee('deficit')
        ->assertDontSee('weeks behind')
        ->assertDontSee('target count')
        ->assertDontSee('% complete');

    Carbon::setTestNow();
})->group('scenario:CO-10');

it('states this week\'s goal and progress in the orders tab', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-17 09:00')); // Monday
    $student = coStudent();
    $this->actingAs($student);

    $modules = SyllabusModule::factory()->count(3)->create();
    foreach ($modules as $i => $module) {
        WeeklyTarget::create([
            'student_id' => $student->id,
            'module_id' => $module->id,
            'week_start_date' => '2026-08-17',
            'is_completed' => false,
        ]);
        if ($i === 0) {
            StudentProgress::create([
                'student_id' => $student->id,
                'module_id' => $module->id,
                'status' => 'mastered',
            ]);
        }
    }

    Livewire::test(CaptainsOrders::class)
        ->assertSee('mission')
        ->assertSee('1 of 3 islands conquered');

    Carbon::setTestNow();
})->group('scenario:CO-11');

it('switches between the Orders, Locker, Journal and Logs tabs', function () {
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->assertSet('tab', 'orders')
        ->call('showTab', 'locker')->assertSet('tab', 'locker')
        ->call('showTab', 'journal')->assertSet('tab', 'journal')
        ->call('showTab', 'logs')->assertSet('tab', 'logs')
        ->call('showTab', 'orders')->assertSet('tab', 'orders');
})->group('scenario:SL-01');

it('shows the master Voyage streak and sub-streaks in the Journal', function () {
    $student = coStudent();
    $this->actingAs($student);
    StudentStreak::create(['student_id' => $student->id, 'type' => 'voyage', 'count' => 3]);
    StudentStreak::create(['student_id' => $student->id, 'type' => 'reading', 'count' => 2]);

    Livewire::test(CaptainsOrders::class)
        ->call('showTab', 'journal')
        ->assertSee('day Voyage streak')
        ->assertSee('Reading')
        ->assertSee('Vocabulary')
        ->assertSee('3');
})->group('scenario:SL-02');

it('shows the day-by-day record in the Logs tab', function () {
    Carbon::setTestNow(Carbon::parse('2026-08-19 10:00'));
    $student = coStudent();
    $this->actingAs($student);
    DailyPlan::create([
        'student_id' => $student->id,
        'date' => '2026-08-18',
        'is_writing_day' => false,
        'duties' => ['vocabulary' => true, 'reading' => true, 'map' => true],
    ]);

    Livewire::test(CaptainsOrders::class)
        ->call('showTab', 'logs')
        ->assertSee('18 Aug')
        ->assertSee('cleared');

    Carbon::setTestNow();
})->group('scenario:SL-03');

it('shows the four rewards, with a longer explanation, in the Locker', function () {
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->call('showTab', 'locker')
        ->assertSee('Shore Leave')
        ->assertSee('Anchor')
        ->assertSee('Tailwind')
        ->assertSee('Lifebuoy')
        ->assertSee('ultimate safety net'); // the longer hover explanation is in the DOM
})->group('scenario:SL-05');

it('uses a held reward from the Locker', function () {
    $student = coStudent();
    $this->actingAs($student);
    app(StreakEconomyService::class)->grantReward($student->id, 'anchor', 'guardian');

    Livewire::test(CaptainsOrders::class)
        ->call('showTab', 'locker')
        ->call('useReward', 'anchor')
        ->assertDispatched('reward-used');

    expect(app(StreakEconomyService::class)->balance($student->id, 'anchor'))->toBe(0);
})->group('scenario:SL-06');

// SL-08 — the Locker shows how rewards are earned and never deflates when empty.
it('shows how to earn rewards and frames an empty Locker as goals', function () {
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->call('showTab', 'locker')
        ->assertSee('rewards to sail toward')   // empty-Locker framing, not a row of zeroes
        ->assertSee('How to earn')              // each reward tells her how to earn it
        ->assertSee('reach milestones');
})->group('scenario:SL-08');

// CO-12 — on a phone the orders are a bottom sheet, so the sea stays in view (never a full cover).
it('renders the orders panel as a mobile-safe bottom sheet', function () {
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->assertSee('data-co12="sheet"', false)   // the bottom-sheet marker on the panel
        ->assertSee('56vh');                        // the mobile rule caps height so map shows above
})->group('scenario:CO-12');
