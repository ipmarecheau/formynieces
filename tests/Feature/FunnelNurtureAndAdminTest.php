<?php

use App\Filament\Resources\Leads\Pages\ListLeads;
use App\Livewire\PlacementReportResult;
use App\Mail\SeaQuestionMail;
use App\Models\Lead;
use App\Models\PracticeQuestion;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

/**
 * lead_capture.feature — weekly nurture (LG-10), strand segmentation (LG-11) and the
 * admin leads panel (LG-14).
 */
it('emails the weekly SEA question only to opted-in leads (LG-10)', function () {
    Mail::fake();
    PracticeQuestion::factory()->create(['is_active' => true, 'options' => ['a', 'b'], 'correct_index' => 0]);
    $in = Lead::factory()->create(['weekly_opt_in' => true]);
    $out = Lead::factory()->create(['weekly_opt_in' => false]);

    $this->artisan('funnel:weekly-question')->assertSuccessful();

    Mail::assertSent(SeaQuestionMail::class, fn ($m) => $m->hasTo($in->email));
    Mail::assertNotSent(SeaQuestionMail::class, fn ($m) => $m->hasTo($out->email));
})->group('scenario:LG-10');

it('lets a lead opt in to the weekly question from the report (LG-10)', function () {
    $lead = Lead::factory()->withReport()->create(['weekly_opt_in' => false]);

    Livewire::test(PlacementReportResult::class, ['leadId' => $lead->id])
        ->set('weeklyOptIn', true);

    expect($lead->fresh()->weekly_opt_in)->toBeTrue();
})->group('scenario:LG-10');

it('segments leads by their child’s weakest strands (LG-11)', function () {
    Lead::factory()->withReport()->create(['weakest_strands' => ['Fractions', 'Spelling']]);
    Lead::factory()->withReport()->create(['weakest_strands' => ['Reading Comprehension']]);

    $fractions = Lead::whereJsonContains('weakest_strands', 'Fractions')->get();
    expect($fractions)->toHaveCount(1);
})->group('scenario:LG-11');

it('shows captured leads with their placement snapshot in the admin panel (LG-14)', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $lead = Lead::factory()->withReport()->create(['email' => 'seen-lead@example.com']);

    $this->actingAs($admin);

    Livewire::test(ListLeads::class)
        ->assertCanSeeTableRecords([$lead])
        ->assertSee('seen-lead@example.com');
})->group('scenario:LG-14');
