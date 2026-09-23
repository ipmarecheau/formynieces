<?php

use App\Models\QaReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['services.qa.token' => 'secret-token']);
});

it('404s the manifest without a valid token', function () {
    $this->getJson('/qa/manifest')->assertNotFound();
    $this->getJson('/qa/manifest?token=wrong')->assertNotFound();
});

it('serves the manifest with features and user stories when the token matches', function () {
    $this->getJson('/qa/manifest?token=secret-token')
        ->assertOk()
        ->assertJsonStructure(['app', 'intent', 'how_to_test', 'features' => [['id', 'name', 'mvp', 'stories']]])
        ->assertJsonPath('app', 'SmoothSeas');
});

it('404s the manifest entirely when no token is configured', function () {
    config(['services.qa.token' => null]);
    $this->getJson('/qa/manifest?token=anything')->assertNotFound();
});

it('accepts a report from the agent and rejects a bad outcome', function () {
    $this->postJson('/qa/report?token=secret-token', [
        'scenario' => 'LL-20',
        'outcome' => 'gap',
        'summary' => 'Competency check did not offer the tutorial option.',
        'detail' => 'Walked the loop; on fail only lesson + practice were shown.',
        'actor' => 'qa-agent',
    ])->assertCreated()->assertJsonPath('ok', true);

    expect(QaReport::where('scenario', 'LL-20')->where('outcome', 'gap')->exists())->toBeTrue();

    $this->postJson('/qa/report?token=secret-token', [
        'outcome' => 'nonsense',
        'summary' => 'x',
    ])->assertStatus(422);
});

it('accepts the report token via header too', function () {
    $this->withHeaders(['X-QA-Token' => 'secret-token'])
        ->postJson('/qa/report', ['outcome' => 'pass', 'summary' => 'Morning reading served a fresh passage.'])
        ->assertCreated();
});

it('shows the reports dashboard with the token', function () {
    QaReport::create(['outcome' => 'fail', 'summary' => 'Voyage point 6 would not open.']);
    $this->get('/qa/reports?token=secret-token')
        ->assertOk()
        ->assertSee('Voyage point 6 would not open.');
    $this->get('/qa/reports')->assertNotFound();
});

it('flags QA accounts as test and excludes them from the real scope', function () {
    $real = User::factory()->create(['role' => 'student', 'is_test' => false]);
    $qa = User::factory()->create(['role' => 'student', 'is_test' => true]);

    $realIds = User::real()->pluck('id');
    expect($realIds)->toContain($real->id);
    expect($realIds)->not->toContain($qa->id);
});
