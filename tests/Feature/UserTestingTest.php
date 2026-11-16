<?php

use App\Livewire\UserTesting;
use App\Models\PracticeAttempt;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\VerificationRun;
use App\Models\WritingSubmission;
use App\Services\UserTesting\TestingCatalog;
use Livewire\Livewire;

/*
 * The guided /user-testing walkthrough: a tester signs in by email, walks a feature's
 * scenarios (back and forward), and each verdict is upserted as a VerificationRun — one per
 * tester per scenario, evidence for Isaac's review, never a specs:verify.
 */

it('opens the walkthrough without logging in', function () {
    $this->get('/user-testing')->assertOk();
});

it('requires a valid email and a name before testing', function () {
    Livewire::test(UserTesting::class)
        ->set('testerEmail', 'not-an-email')
        ->set('testerName', '')
        ->call('signIn')
        ->assertHasErrors(['testerEmail', 'testerName']);

    Livewire::test(UserTesting::class)
        ->set('testerEmail', 'Maria@Example.com')
        ->set('testerName', 'Maria')
        ->call('signIn')
        ->assertHasNoErrors();

    expect(session('tester_email'))->toBe('maria@example.com'); // normalised
    expect(session('tester_name'))->toBe('Maria');
});

it('restores a browser-remembered tester (email is the durable key) and reloads their verdicts', function () {
    VerificationRun::factory()->create([
        'tester_email' => 'maria@example.com',
        'scenario_id' => 'HC-01',
        'feature' => 'HC',
        'result' => VerificationRun::RESULT_WORKED,
    ]);

    Livewire::test(UserTesting::class, ['feature' => 'hc'])
        ->call('restoreIdentity', 'Maria@Example.com', 'Maria') // as the browser would, from localStorage
        ->assertSet('testerEmail', 'maria@example.com')         // normalised
        ->assertSet('testerName', 'Maria')
        ->assertSee('Your last answer'); // their prior verdict is shown again

    expect(session('tester_email'))->toBe('maria@example.com');
})->group('scenario:UT-01');

it('ignores an invalid remembered identity', function () {
    Livewire::test(UserTesting::class)
        ->call('restoreIdentity', 'not-an-email', '')
        ->assertSet('testerEmail', '');

    expect(session('tester_email'))->toBeNull();
});

it('records a verdict and advances to the next scenario', function () {
    Livewire::test(UserTesting::class, ['feature' => 'hc'])
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria')
        ->set('notes', 'Button was easy to find')
        ->call('record', VerificationRun::RESULT_WORKED)
        ->assertSet('index', 1);

    $run = VerificationRun::sole();
    expect($run->tester_email)->toBe('maria@example.com');
    expect($run->scenario_id)->toBe('HC-01');
    expect($run->result)->toBe(VerificationRun::RESULT_WORKED);
    expect($run->notes)->toBe('Button was easy to find');
})->group('scenario:UT-02');

it('upserts — changing an answer updates the same row, never duplicates', function () {
    $c = Livewire::test(UserTesting::class, ['feature' => 'hc'])
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria')
        ->call('record', VerificationRun::RESULT_WORKED); // HC-01, now at index 1

    $c->call('prev') // back to HC-01
        ->set('notes', 'Actually the button was hidden')
        ->call('record', VerificationRun::RESULT_ISSUE);

    $run = VerificationRun::where('scenario_id', 'HC-01')->sole();
    expect($run->result)->toBe(VerificationRun::RESULT_ISSUE);
    expect($run->notes)->toBe('Actually the button was hidden');
    expect(VerificationRun::where('scenario_id', 'HC-01')->count())->toBe(1);
})->group('scenario:UT-03');

it('shows a tester their saved note when they navigate back', function () {
    VerificationRun::factory()->create([
        'tester_email' => 'maria@example.com',
        'scenario_id' => 'HC-02',
        'notes' => 'saved earlier',
    ]);

    // HC-02 is the second scenario (index 1).
    Livewire::test(UserTesting::class, ['feature' => 'hc'])
        ->set('testerEmail', 'maria@example.com')
        ->call('goTo', 1)
        ->assertSet('notes', 'saved earlier');
});

it('does not record anything until a tester is identified', function () {
    Livewire::test(UserTesting::class, ['feature' => 'hc'])
        ->set('testerEmail', '')
        ->call('record', VerificationRun::RESULT_WORKED);

    expect(VerificationRun::count())->toBe(0);
});

it('walks every seeded scenario of a feature in order', function () {
    $ids = collect(TestingCatalog::feature('HC')['scenarios'])->pluck('id');

    $component = Livewire::test(UserTesting::class, ['feature' => 'hc'])
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria');

    foreach ($ids as $expectedId) {
        $component->assertSee($expectedId)
            ->call('record', VerificationRun::RESULT_WORKED);
    }

    $component->assertSet('index', $ids->count());
    expect(VerificationRun::where('tester_email', 'maria@example.com')->pluck('scenario_id')->sort()->values()->all())
        ->toBe($ids->sort()->values()->all());
});

/*
 * The seeded auto-login "enter" endpoint: it prepares each scenario's state and signs the
 * tester in as its dedicated is_test account — hard-gated, and never a real user.
 */

it('is refused when impersonation is disabled', function () {
    config()->set('user_testing.impersonation', false);

    $this->get('/user-testing/enter/HC-01')->assertForbidden();
})->group('scenario:UT-04');

it('404s for an unknown scenario', function () {
    config()->set('user_testing.impersonation', true);

    $this->get('/user-testing/enter/ZZ-99')->assertNotFound();
});

it('signs in as a seeded is_test parent and lands on the dashboard', function () {
    config()->set('user_testing.impersonation', true);

    $this->get('/user-testing/enter/HC-01')->assertRedirect('/guardian/dashboard');

    $user = auth()->user();
    expect($user)->not->toBeNull();
    expect($user->is_test)->toBeTrue();
    expect($user->role)->toBe('guardian');
    // Must be email-verified, or the dashboard bounces to the verification gate.
    expect($user->hasVerifiedEmail())->toBeTrue();
});

it('seeds verified accounts so no feature bounces to the email-verification gate', function () {
    config()->set('user_testing.impersonation', true);

    // Parent, child, and admin personas must all land verified.
    $this->get('/user-testing/enter/HC-01');
    expect(auth()->user()->hasVerifiedEmail())->toBeTrue();

    $this->get('/user-testing/enter/HC-03'); // child persona
    expect(auth()->user()->hasVerifiedEmail())->toBeTrue();

    $this->get('/user-testing/enter/AL-01'); // admin persona
    expect(auth()->user()->hasVerifiedEmail())->toBeTrue();
});

it('signs nobody in for a guest scenario', function () {
    config()->set('user_testing.impersonation', true);

    $this->get('/user-testing/enter/HC-05')->assertRedirect('/');
    expect(auth()->check())->toBeFalse();
});

it('seeds a full activity history for the admin logs scenario', function () {
    config()->set('user_testing.impersonation', true);

    $this->get('/user-testing/enter/AL-02')->assertRedirect('/admin/student-logs');

    $admin = auth()->user();
    expect($admin->is_test)->toBeTrue();
    expect($admin->role)->toBe('admin');

    // The child the admin will pick must actually have varied activity to show.
    $child = User::where('role', 'student')->where('is_test', true)->latest('id')->first();
    expect($child)->not->toBeNull();
    expect(PracticeAttempt::where('student_id', $child->id)->exists())->toBeTrue();
    expect(WritingSubmission::where('student_id', $child->id)->exists())->toBeTrue();
    expect(SupportTicket::where('student_id', $child->id)->exists())->toBeTrue();
});

it('is idempotent — re-entering a scenario does not pile up duplicate accounts', function () {
    config()->set('user_testing.impersonation', true);

    $this->get('/user-testing/enter/HC-01');
    $this->get('/user-testing/enter/HC-01');

    expect(User::where('email', 'like', 'td-hc-01-%')->count())->toBe(2); // one guardian + one child, not four
});

/*
 * Device features (install the app): they render a QR + permissions walkthrough instead of
 * an embedded preview, and their verdicts record the same way.
 */

it('shows a QR install code and permissions for a device feature', function () {
    Livewire::test(UserTesting::class, ['feature' => 'lr'])
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria')
        ->assertSet('index', 0)
        ->assertSee('Install the app')
        ->assertSee('Allow notifications')
        ->assertSeeHtml('data:image/svg+xml;base64,'); // the rendered QR
});

it('records a verdict on a device scenario', function () {
    Livewire::test(UserTesting::class, ['feature' => 'lr'])
        ->set('testerEmail', 'maria@example.com')
        ->set('testerName', 'Maria')
        ->call('record', VerificationRun::RESULT_WORKED);

    $run = VerificationRun::sole();
    expect($run->feature)->toBe('LR');
    expect($run->scenario_id)->toBe('LR-01');
});

it('does not try to auto-login for device scenarios (no enter route needed)', function () {
    // Device scenarios have no persona/start; the catalog marks the feature as device.
    expect(TestingCatalog::isDevice(TestingCatalog::feature('LR')))->toBeTrue();
    expect(TestingCatalog::isDevice(TestingCatalog::feature('HC')))->toBeFalse();
});
