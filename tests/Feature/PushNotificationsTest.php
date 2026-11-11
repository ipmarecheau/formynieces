<?php

use App\Models\DeviceToken;
use App\Models\PushDelivery;
use App\Models\User;
use App\Services\Push\Notifications\BillingNotifier;
use App\Services\Push\Notifications\EssayFeedbackNotifier;
use App\Services\Push\Notifications\MilestoneNotifier;
use App\Services\Push\PushCriticality;
use App\Services\Push\PushMessage;
use App\Services\Push\PushResult;
use App\Services\Push\PushSender;
use App\Services\Push\PushTransportRegistry;
use App\Services\Push\Transports\ApnsTransport;
use App\Services\Push\Transports\FcmTransport;
use Tests\Support\FakePushTransport;

/*
 * Behaviour coverage for formynieces-spec/features/push_notifications.feature (PN-01..08).
 * The transports are faked (bound on the real registry singleton) so no test ever contacts
 * Apple or Google. Each test is tagged with the scenario id it realises for `specs:trace`.
 */

/**
 * Swap the real APNs/FCM transports on the container singleton for in-memory fakes and
 * return them so a test can assert what was sent.
 *
 * @return array{ios: FakePushTransport, android: FakePushTransport}
 */
function fakePushTransports(): array
{
    $ios = new FakePushTransport('ios');
    $android = new FakePushTransport('android');

    $registry = new PushTransportRegistry;
    $registry->register($ios)->register($android);
    app()->instance(PushTransportRegistry::class, $registry);

    return ['ios' => $ios, 'android' => $android];
}

it('registers a device push token against the user and platform, updating on repeat', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('push.token'), ['token' => 'abc123', 'platform' => 'ios'])
        ->assertCreated()->assertJson(['ok' => true]);

    // Re-registering the SAME token updates rather than duplicating.
    $this->actingAs($user)
        ->postJson(route('push.token'), ['token' => 'abc123', 'platform' => 'ios'])
        ->assertCreated();

    expect(DeviceToken::where('token', 'abc123')->count())->toBe(1);
    expect($user->deviceTokens()->first()->platform)->toBe('ios');
})->group('scenario:PN-01');

it('requires authentication to register a device token', function () {
    // A guest is blocked by the auth middleware (redirected to login), never stored.
    $this->post(route('push.token'), ['token' => 'x', 'platform' => 'ios'])
        ->assertRedirect(route('login'));
    expect(DeviceToken::where('token', 'x')->exists())->toBeFalse();
})->group('scenario:PN-01');

it('prunes a dead token the gateway rejects but keeps a merely rate-limited one', function () {
    $fakes = fakePushTransports();
    $user = User::factory()->create();
    DeviceToken::factory()->ios()->for($user)->create(['token' => 'dead']);
    DeviceToken::factory()->android()->for($user)->create(['token' => 'busy']);

    $fakes['ios']->rejectAsUnregistered('dead');
    $fakes['android']->rejectAsRetryable('busy');

    app(PushSender::class)
        ->send(PushMessage::standard($user->id, 'ping', 'Hi', 'There'));

    // Dead token gone; rate-limited token kept for a later retry.
    expect(DeviceToken::where('token', 'dead')->exists())->toBeFalse();
    expect(DeviceToken::where('token', 'busy')->exists())->toBeTrue();

    expect(PushDelivery::where('outcome', PushDelivery::OUTCOME_FAILED)->where('reason', 'token-pruned')->exists())->toBeTrue();
    expect(PushDelivery::where('outcome', PushDelivery::OUTCOME_RETRYABLE)->exists())->toBeTrue();
})->group('scenario:PN-02');

it('suppresses non-critical pushes under opt-out or quiet hours, but not critical ones', function () {
    fakePushTransports();
    $user = User::factory()->create(['push_opt_out' => true]);
    DeviceToken::factory()->ios()->for($user)->create();
    $sender = app(PushSender::class);

    $sender->send(PushMessage::standard($user->id, 'streak', 'Nice', '5 days!'));
    expect(PushDelivery::where('type', 'streak')->first()->outcome)->toBe(PushDelivery::OUTCOME_SUPPRESSED);

    // A critical push ignores opt-out entirely.
    $sender->send(PushMessage::critical($user->id, 'billing.renewal', 'Heads up', 'Renews Friday'));
    expect(PushDelivery::where('type', 'billing.renewal')->first()->outcome)->toBe(PushDelivery::OUTCOME_DELIVERED);

    // Quiet-hours maths (wraps midnight) is honoured too.
    $quiet = User::factory()->create(['quiet_hours_start' => '20:45', 'quiet_hours_end' => '07:00']);
    expect($quiet->isWithinQuietHours(now()->setTime(22, 0)))->toBeTrue();
    expect($quiet->isWithinQuietHours(now()->setTime(12, 0)))->toBeFalse();
    expect(PushCriticality::Critical->respectsQuietHours())->toBeFalse();
})->group('scenario:PN-03');

it('pushes the parent when a teacher publishes essay feedback', function () {
    $fakes = fakePushTransports();
    $guardian = User::factory()->create(['role' => 'guardian']);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'name' => 'Maya Khan']);
    DeviceToken::factory()->ios()->for($guardian)->create(['token' => 'parent-phone']);
    DeviceToken::factory()->ios()->for($child)->create(['token' => 'child-tablet']);

    app(EssayFeedbackNotifier::class)->published($child, essayId: 42);

    // Parent's own device got it; the child's tablet did not.
    expect($fakes['ios']->wasSentTo('parent-phone'))->toBeTrue();
    expect($fakes['ios']->wasSentTo('child-tablet'))->toBeFalse();

    $sent = collect($fakes['ios']->sent)->firstWhere('token', 'parent-phone')['message'];
    expect($sent->data['deep_link'])->toBe('/essays/42/feedback');
    expect($sent->body)->toContain('Maya');
})->group('scenario:PN-04');

it('pushes the parent on another device when a child hits a milestone', function () {
    $fakes = fakePushTransports();
    $guardian = User::factory()->create(['role' => 'guardian']);
    $child = User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'name' => 'Leo Ali']);
    DeviceToken::factory()->android()->for($guardian)->create(['token' => 'parent-droid']);
    DeviceToken::factory()->android()->for($child)->create(['token' => 'child-droid']);

    app(MilestoneNotifier::class)->reached($child, 'Full day complete! ⚓', 'finished every task today.');

    expect($fakes['android']->wasSentTo('parent-droid'))->toBeTrue();
    expect($fakes['android']->wasSentTo('child-droid'))->toBeFalse();
})->group('scenario:PN-05');

it('sends a single re-engagement push when a child has drifted away', function () {
    fakePushTransports();
    $guardian = User::factory()->create(['role' => 'guardian']);
    User::factory()->create(['role' => 'student', 'parent_id' => $guardian->id, 'is_test' => false]);
    DeviceToken::factory()->ios()->for($guardian)->create();

    // The child has no practice activity at all — idle by definition.
    $this->artisan('push:re-engage --days=3')->assertSuccessful();
    expect(PushDelivery::where('type', 're-engagement')->where('user_id', $guardian->id)->count())->toBe(1);

    // Running again the same night does NOT nudge a second time (cooldown).
    $this->artisan('push:re-engage --days=3')->assertSuccessful();
    expect(PushDelivery::where('type', 're-engagement')->where('user_id', $guardian->id)->count())->toBe(1);
})->group('scenario:PN-06');

it('pushes the account owner on a billing event regardless of quiet hours', function () {
    fakePushTransports();
    // Opted out AND inside quiet hours — a critical push still lands.
    $owner = User::factory()->create([
        'role' => 'guardian', 'push_opt_out' => true,
        'quiet_hours_start' => '00:00', 'quiet_hours_end' => '23:59',
    ]);
    DeviceToken::factory()->ios()->for($owner)->create();

    app(BillingNotifier::class)->paymentFailed($owner);

    $delivery = PushDelivery::where('type', 'billing.failed')->firstOrFail();
    expect($delivery->outcome)->toBe(PushDelivery::OUTCOME_DELIVERED);
    expect($delivery->criticality)->toBe('critical');
})->group('scenario:PN-07');

it('dispatches iOS via APNs and Android via FCM directly and logs every attempt', function () {
    $fakes = fakePushTransports();
    $user = User::factory()->create();
    DeviceToken::factory()->ios()->for($user)->create(['token' => 'ios-tok']);
    DeviceToken::factory()->android()->for($user)->create(['token' => 'android-tok']);

    app(PushSender::class)
        ->send(PushMessage::standard($user->id, 'milestone', 'Yay', 'Great work'));

    // Each platform went through its own transport.
    expect($fakes['ios']->wasSentTo('ios-tok'))->toBeTrue();
    expect($fakes['android']->wasSentTo('android-tok'))->toBeTrue();

    // The real transports are the direct, no-Firebase implementations.
    $registry = (new PushTransportRegistry)
        ->register(new ApnsTransport)->register(new FcmTransport);
    expect($registry->for('ios'))->toBeInstanceOf(ApnsTransport::class);
    expect($registry->for('android'))->toBeInstanceOf(FcmTransport::class);

    // Every attempt is logged for the admin panel.
    expect(PushDelivery::where('user_id', $user->id)->count())->toBe(2);
    expect(PushDelivery::where('platform', 'ios')->where('outcome', 'delivered')->exists())->toBeTrue();
    expect(PushDelivery::where('platform', 'android')->where('outcome', 'delivered')->exists())->toBeTrue();
})->group('scenario:PN-08');

it('builds value objects for each terminal outcome', function () {
    expect(PushResult::delivered('t')->isDelivered())->toBeTrue();
    expect(PushResult::unregistered('t')->shouldPruneToken())->toBeTrue();
    expect(PushResult::retryable('t')->shouldPruneToken())->toBeFalse();
    expect(PushResult::failed('t')->isDelivered())->toBeFalse();
})->group('scenario:PN-08');
