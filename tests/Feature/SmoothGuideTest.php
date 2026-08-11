<?php

use App\Livewire\SmoothGuide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('auto-opens on a first visit and can be dismissed', function () {
    $student = User::factory()->create(['role' => 'student', 'seen_guides' => null]);

    Livewire::actingAs($student)
        ->test(SmoothGuide::class, ['guide' => 'practice'])
        ->assertSet('open', true)                 // first visit → shown
        ->assertSee('How practice works')
        ->call('dismiss')
        ->assertSet('open', false);

    expect($student->fresh()->hasSeenGuide('practice'))->toBeTrue();
})->group('scenario:SG-01');

it('does not auto-open once dismissed, but can be reopened on demand', function () {
    $student = User::factory()->create(['role' => 'student', 'seen_guides' => ['practice']]);

    Livewire::actingAs($student)
        ->test(SmoothGuide::class, ['guide' => 'practice'])
        ->assertSet('open', false)                // already seen → does not nag
        ->call('reopen')
        ->assertSet('open', true)
        ->assertSee('How practice works');
})->group('scenario:SG-02');

it('practice guide explains the three levels, two attempts and first-try mastery', function () {
    $student = User::factory()->create(['role' => 'student']);

    Livewire::actingAs($student)
        ->test(SmoothGuide::class, ['guide' => 'practice'])
        ->assertSeeText('three levels')
        ->assertSeeText('two tries')
        ->assertSeeText('FIRST try');
})->group('scenario:SG-03');

it('voyage guide explains map progression', function () {
    $student = User::factory()->create(['role' => 'student']);

    Livewire::actingAs($student)
        ->test(SmoothGuide::class, ['guide' => 'voyage'])
        ->assertSeeText('glowing islands')
        ->assertSeeText('unlock the next');
})->group('scenario:SG-04');

it('island guide explains conquering stops in order', function () {
    $student = User::factory()->create(['role' => 'student']);

    Livewire::actingAs($student)
        ->test(SmoothGuide::class, ['guide' => 'island'])
        ->assertSeeText('follow the trail in order')
        ->assertSeeText('master the whole island');
})->group('scenario:SG-04');

it('never puts a guardian-layer metric in any guide copy', function () {
    // The child-layer rule applies to what Smooth SAYS — the guide's title and lines,
    // not the stylesheet — so assert on the content source, never the rendered CSS.
    foreach (array_keys(config('guides')) as $guide) {
        $content = config("guides.{$guide}");
        $copy = strtolower($content['title'].' '.implode(' ', $content['lines']));

        expect($copy)
            ->not->toContain('%')
            ->not->toContain('pace')
            ->not->toContain('percent')
            ->not->toContain('target');
    }
})->group('scenario:SG-05');
