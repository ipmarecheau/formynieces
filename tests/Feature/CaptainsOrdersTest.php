<?php

use App\Livewire\CaptainsOrders;
use App\Models\StudentStreak;
use App\Models\User;
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
        ->assertSee('Captain')   // "Captain's Orders" (apostrophe renders as entity)
        ->assertSee('Brief')
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

it('switches between the Brief and Ship\'s Log tabs', function () {
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->assertSet('tab', 'brief')
        ->assertSee('Ship')      // "Ship's Log" tab
        ->call('showTab', 'log')
        ->assertSet('tab', 'log')
        ->call('showTab', 'brief')
        ->assertSet('tab', 'brief');
})->group('scenario:SL-01');

it('shows the master Voyage streak and sub-streaks in the log', function () {
    $student = coStudent();
    $this->actingAs($student);
    StudentStreak::create(['student_id' => $student->id, 'type' => 'voyage', 'count' => 3]);
    StudentStreak::create(['student_id' => $student->id, 'type' => 'reading', 'count' => 2]);

    Livewire::test(CaptainsOrders::class)
        ->call('showTab', 'log')
        ->assertSee('day Voyage streak')
        ->assertSee('Reading')
        ->assertSee('Vocabulary')
        ->assertSee('3');
})->group('scenario:SL-02');

it('shows the four rewards in the Captain\'s Locker', function () {
    $this->actingAs(coStudent());

    Livewire::test(CaptainsOrders::class)
        ->call('showTab', 'log')
        ->assertSee('Locker')
        ->assertSee('Shore Leave')
        ->assertSee('Anchor')
        ->assertSee('Tailwind')
        ->assertSee('Lifebuoy');
})->group('scenario:SL-05');

it('uses a held reward from the Locker', function () {
    $student = coStudent();
    $this->actingAs($student);
    app(StreakEconomyService::class)->grantReward($student->id, 'anchor', 'guardian');

    Livewire::test(CaptainsOrders::class)
        ->call('showTab', 'log')
        ->call('useReward', 'anchor')
        ->assertDispatched('reward-used');

    expect(app(StreakEconomyService::class)->balance($student->id, 'anchor'))->toBe(0);
})->group('scenario:SL-06');
