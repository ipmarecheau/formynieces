<?php

use App\Livewire\PlacementReport;
use App\Models\Lead;
use Livewire\Livewire;

/**
 * lead_capture.feature — the funnel's entrance: the offer, the email/WhatsApp capture,
 * and a returning lead skipping straight past it (LG-01/02/13).
 */
it('offers the free mock and placement report, for an email (LG-01)', function () {
    $this->get(route('placement-report'))
        ->assertOk()
        ->assertSee('first-choice school')
        ->assertSee('placement report')
        ->assertSee('SEA syllabus')
        ->assertSee('WhatsApp number');
})->group('scenario:LG-01');

it('captures a lead on submit and moves into the mock (LG-02)', function () {
    Livewire::test(PlacementReport::class)
        ->set('email', 'parent@example.com')
        ->set('childLevel', 'Standard 4')
        ->set('whatsapp', '+1 868 555 0100')
        ->call('beginMock')
        ->assertHasNoErrors()
        ->assertSet('phase', 'mock');

    $lead = Lead::first();
    expect($lead->email)->toBe('parent@example.com')
        ->and($lead->child_level)->toBe('Standard 4')
        ->and($lead->whatsapp)->toBe('+1 868 555 0100')
        ->and($lead->source)->toBe('placement-report');
})->group('scenario:LG-02');

it('validates the email and child level before capturing (LG-02)', function () {
    Livewire::test(PlacementReport::class)
        ->set('email', 'not-an-email')
        ->set('childLevel', '')
        ->call('beginMock')
        ->assertHasErrors(['email', 'childLevel']);

    expect(Lead::count())->toBe(0);
})->group('scenario:LG-02');

it('lets a returning lead skip capture and go straight to the mock (LG-13)', function () {
    $lead = Lead::factory()->create();

    session(['lead_id' => $lead->id]);

    Livewire::test(PlacementReport::class)
        ->assertSet('phase', 'mock')
        ->assertSet('leadId', $lead->id);
})->group('scenario:LG-13');

it('sends a returning lead who already has a report to the report (LG-13)', function () {
    $lead = Lead::factory()->withReport()->create();

    session(['lead_id' => $lead->id]);

    Livewire::test(PlacementReport::class)
        ->assertSet('phase', 'report');
})->group('scenario:LG-13');
