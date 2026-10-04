<?php

use App\Models\DeviceToken;
use App\Models\PracticeAttempt;
use App\Models\PracticeQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a device token against the signed-in user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('device-tokens.store'), [
            'token' => 'fcm-token-abc',
            'platform' => 'android',
            'app' => 'child',
        ])
        ->assertOk()
        ->assertJsonPath('registered', true);

    $this->assertDatabaseHas(DeviceToken::class, [
        'user_id' => $user->id, 'token' => 'fcm-token-abc', 'platform' => 'android', 'app' => 'child',
    ]);
});

it('upserts the same token instead of duplicating it', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->postJson(route('device-tokens.store'), ['token' => 'tok-1', 'app' => 'child'])->assertOk();
    $this->actingAs($user)->postJson(route('device-tokens.store'), ['token' => 'tok-1', 'app' => 'parent'])->assertOk();

    expect(DeviceToken::where('token', 'tok-1')->count())->toBe(1);
    expect(DeviceToken::where('token', 'tok-1')->first()->app)->toBe('parent'); // latest wins
});

it('requires authentication to register a token', function () {
    // Web route → the auth middleware redirects an unauthenticated request to login.
    $this->postJson(route('device-tokens.store'), ['token' => 'nope'])->assertStatus(302);
});

it('validates the token is present', function () {
    $this->actingAs(User::factory()->create())
        ->postJson(route('device-tokens.store'), ['app' => 'child'])
        ->assertInvalid(['token']);
});

it('streak-reminder dry run targets students with a device who have not practised today', function () {
    // A student with the app who HAS practised today — excluded.
    $practised = User::factory()->create(['role' => 'student']);
    DeviceToken::create(['user_id' => $practised->id, 'token' => 't-practised', 'app' => 'child']);
    $question = PracticeQuestion::factory()->create();
    PracticeAttempt::create([
        'student_id' => $practised->id, 'practice_question_id' => $question->id, 'module_id' => $question->module_id,
        'difficulty' => 1, 'attempt' => 1, 'is_correct' => true,
    ]);

    // A student with the app who has NOT practised today — included.
    $idle = User::factory()->create(['role' => 'student']);
    DeviceToken::create(['user_id' => $idle->id, 'token' => 't-idle', 'app' => 'child']);

    // A student with NO app installed — excluded.
    User::factory()->create(['role' => 'student']);

    $this->artisan('notify:streak-reminders --dry-run')
        ->expectsOutputToContain('Students to remind: 1')
        ->assertExitCode(0);
});
