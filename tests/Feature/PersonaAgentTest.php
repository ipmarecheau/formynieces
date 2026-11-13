<?php

use App\Filament\Resources\PersonaFindings\Pages\ListPersonaFindings;
use App\Models\PersonaFinding;
use App\Models\PersonaRun;
use App\Models\User;
use App\Services\Persona\Contracts\PersonaSimulator;
use App\Services\Persona\PersonaBudget;
use App\Services\Persona\PersonaTestingService;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Tests\Support\FakePersonaSimulator;

/*
 * The nightly persona-simulation agent (PA-01..06): schedule, budget, run/finding recording,
 * and admin critique — all against a stand-in simulator so no browser or LLM is touched.
 */

function bindFakeSimulator(float $cost = 0.05, bool $throws = false): void
{
    app()->instance(PersonaSimulator::class, new FakePersonaSimulator($cost, $throws));
}

it('records a run with its findings, summary and cost', function () {
    bindFakeSimulator(0.07);

    $runs = app(PersonaTestingService::class)->runNightly();

    $run = $runs[0];
    expect($run->status)->toBe(PersonaRun::STATUS_COMPLETED);
    expect((float) $run->cost_usd)->toBe(0.07);
    expect($run->summary)->not->toBeNull();
    expect($run->findings)->toHaveCount(2);
})->group('scenario:PA-01', 'scenario:PA-05');

it('walks both personas within budget', function () {
    bindFakeSimulator();

    $runs = app(PersonaTestingService::class)->runNightly();

    expect(collect($runs)->pluck('persona')->all())
        ->toBe([PersonaRun::PERSONA_ANXIOUS_PARENT, PersonaRun::PERSONA_CHILD_FULL_DAY]);
    expect(collect($runs)->every(fn ($r) => $r->status === PersonaRun::STATUS_COMPLETED))->toBeTrue();
})->group('scenario:PA-03');

it('skips runs once the monthly budget is reached', function () {
    config()->set('services.persona_agent.monthly_cap_usd', 1.0);
    PersonaRun::factory()->create(['cost_usd' => 1.00, 'created_at' => now()]); // month-to-date = cap
    bindFakeSimulator(0.05);

    $runs = app(PersonaTestingService::class)->runNightly();

    expect(collect($runs)->every(fn ($r) => $r->status === PersonaRun::STATUS_SKIPPED_BUDGET))->toBeTrue();
    expect(PersonaFinding::count())->toBe(0);
})->group('scenario:PA-02');

it('reports budget state', function () {
    config()->set('services.persona_agent.monthly_cap_usd', 5.0);
    PersonaRun::factory()->create(['cost_usd' => 1.25, 'created_at' => now()]);
    PersonaRun::factory()->create(['cost_usd' => 0.75, 'created_at' => now()->subMonth()]); // last month ignored

    $budget = app(PersonaBudget::class);
    expect($budget->spentThisMonth())->toBe(1.25);
    expect($budget->remaining())->toBe(3.75);
    expect($budget->canRun())->toBeTrue();
})->group('scenario:PA-02');

it('records a failed walk without stopping the other persona', function () {
    bindFakeSimulator(throws: true);

    $runs = app(PersonaTestingService::class)->runNightly();

    expect($runs)->toHaveCount(2);
    expect(collect($runs)->every(fn ($r) => $r->status === PersonaRun::STATUS_FAILED))->toBeTrue();
    expect($runs[0]->summary)->toContain('Run failed');
})->group('scenario:PA-06');

it('runs the nightly command', function () {
    bindFakeSimulator();

    $this->artisan('personas:run')->assertSuccessful();

    expect(PersonaRun::where('status', PersonaRun::STATUS_COMPLETED)->count())->toBe(2);
})->group('scenario:PA-03');

it('lets an admin critique a finding with a verdict and notes', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $finding = PersonaFinding::factory()->create(['admin_verdict' => null]);

    Livewire::test(ListPersonaFindings::class)
        ->callAction(TestAction::make('critique')->table($finding), [
            'admin_verdict' => PersonaFinding::VERDICT_VALID,
            'admin_notes' => 'Reproduced — ticketed.',
        ]);

    $fresh = $finding->fresh();
    expect($fresh->admin_verdict)->toBe(PersonaFinding::VERDICT_VALID);
    expect($fresh->admin_notes)->toBe('Reproduced — ticketed.');
})->group('scenario:PA-04');
