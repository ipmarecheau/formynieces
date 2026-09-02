<?php

use App\Livewire\PlacementMock;
use App\Livewire\PlacementReportResult;
use App\Mail\PlacementReportMail;
use App\Models\Lead;
use App\Models\PracticeQuestion;
use App\Services\Funnel\AdminNotifier;
use App\Services\Funnel\PlacementReportService;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/**
 * lead_capture.feature — the mock, the grading, the report and its email delivery
 * (LG-03/04/05/06). WhatsApp to parents is not used; delivery is email only.
 */
function seedMockBank(int $n = 8): void
{
    config()->set('funnel.mock_questions', $n);
    foreach (range(1, $n) as $i) {
        PracticeQuestion::factory()->create([
            'subject' => 'Math',
            'strand' => $i <= 3 ? 'Fractions' : 'Number Concepts',
            'prompt' => "Q{$i}?",
            'options' => ['a', 'b', 'c', 'd'],
            'correct_index' => 0,
            'is_active' => true,
        ]);
    }
}

it('serves a short mock from the SEA-aligned practice bank (LG-03)', function () {
    seedMockBank(8);
    $lead = Lead::factory()->create();

    Livewire::test(PlacementMock::class, ['leadId' => $lead->id])
        ->assertSet('questions', fn ($q) => count($q) === 8);
})->group('scenario:LG-03');

it('grades the mock into a first-choice placement report and emails it (LG-04/05)', function () {
    Mail::fake();
    seedMockBank(8);
    $lead = Lead::factory()->create();

    $t = Livewire::test(PlacementMock::class, ['leadId' => $lead->id]);
    // Answer every question WRONG in Fractions (index 1), correct elsewhere (index 0),
    // so Fractions surfaces as the weakest strand.
    foreach ($t->get('questions') as $q) {
        $t->call('answer', str_contains($q['strand'], 'Fractions') ? 1 : 0);
    }

    $t->assertDispatched('mock-complete');

    $lead->refresh();
    expect($lead->placement_band)->not->toBeNull()
        ->and($lead->mock_score)->toBeInt()
        ->and($lead->weakest_strands)->toContain('Fractions')
        ->and($lead->next_step)->not->toBeNull();

    Mail::assertSent(PlacementReportMail::class, fn ($m) => $m->hasTo($lead->email));
})->group('scenario:LG-04')->group('scenario:LG-05');

it('shows the report with a shareable SEA-Ready card and the offer (LG-04/06/07)', function () {
    $lead = Lead::factory()->withReport()->create(['mock_score' => 78]);

    Livewire::test(PlacementReportResult::class, ['leadId' => $lead->id])
        ->assertSee($lead->placement_band)
        ->assertSee('SEA-Ready score')
        ->assertSee('78%')
        ->assertSee('Share our SEA-Ready score')
        ->assertSee('Start your free month');
})->group('scenario:LG-04')->group('scenario:LG-06')->group('scenario:LG-07');

it('computes bands and weakest strands correctly', function () {
    $svc = app(PlacementReportService::class);
    expect($svc->band(85))->toContain('On track')
        ->and($svc->band(65))->toContain('Within reach')
        ->and($svc->band(40))->toContain('catch-up');

    $graded = collect([
        ['strand' => 'Fractions', 'correct' => false],
        ['strand' => 'Fractions', 'correct' => false],
        ['strand' => 'Spelling', 'correct' => false],
        ['strand' => 'Number', 'correct' => true],
    ]);
    $report = $svc->compute($graded);
    expect($report['score'])->toBe(25)
        ->and($report['weakest_strands'][0])->toBe('Fractions')
        ->and($report['next_step'])->toContain('Fractions');
})->group('scenario:LG-04');

it('the admin notifier is a no-op when unconfigured (LG-12)', function () {
    config()->set('funnel.admin_whatsapp', null);
    $lead = Lead::factory()->create();

    app(AdminNotifier::class)->leadCaptured($lead);

    expect(true)->toBeTrue(); // reached here without throwing → no-op succeeded
})->group('scenario:LG-12');
