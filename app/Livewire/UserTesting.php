<?php

namespace App\Livewire;

use App\Models\VerificationRun;
use App\Services\UserTesting\ExpectedNotificationTime;
use App\Services\UserTesting\ScenarioChangeLog;
use App\Services\UserTesting\ScenarioFingerprint;
use App\Services\UserTesting\ScenarioFixtures;
use App\Services\UserTesting\TestingCatalog;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * The guided test-drive at /user-testing. A tester signs in with their email (which uniquely
 * identifies them so their verdicts persist across sessions and devices), then walks a
 * feature's scenarios one at a time — the real screen embedded alongside the steps, at a
 * Web / Tablet / Mobile viewport — moving back and forward freely. Each verdict is an
 * upsert (one per tester per scenario): evidence for Isaac's review, never a specs:verify.
 */
#[Layout('components.layouts.testing')]
class UserTesting extends Component
{
    public string $testerEmail = '';

    public string $testerName = '';

    /** The tester's IANA timezone (captured from their browser), so reminders land locally. */
    public ?string $testerTimezone = null;

    /** The feature code being walked, or null on the chooser screen. */
    public ?string $feature = null;

    /** Index of the current scenario within the feature. */
    public int $index = 0;

    public string $notes = '';

    public function mount(?string $feature = null): void
    {
        $this->testerEmail = (string) session('tester_email', '');
        $this->testerName = (string) session('tester_name', '');
        $this->testerTimezone = session('tester_timezone') ?: null;
        $this->feature = $feature ? strtoupper($feature) : null;

        // A reminder email can deep-link straight to the scenario it is about, optionally
        // pre-filling the "it never arrived" note so the tester only has to confirm it.
        if ($this->feature !== null && ($jump = request()->string('scenario')->toString()) !== '') {
            $scenarios = collect(TestingCatalog::feature($this->feature)['scenarios'] ?? []);
            $at = $scenarios->search(fn ($s) => $s['id'] === strtoupper($jump));
            if ($at !== false) {
                $this->index = $at;
            }
        }

        $this->syncNotesToSavedRun();

        if (request()->string('outcome')->toString() === 'missed' && $this->notes === '') {
            $this->notes = 'Notification not received.';
        }
    }

    /**
     * Remember the tester's browser timezone, so deferred-check reminders are scheduled against
     * the scenario's notification time in their own local day.
     */
    public function setTimezone(string $timezone): void
    {
        $timezone = trim($timezone);

        if ($timezone === '' || ! in_array($timezone, timezone_identifiers_list(), true)) {
            return;
        }

        $this->testerTimezone = $timezone;
        session(['tester_timezone' => $timezone]);
    }

    public function signIn(): void
    {
        $this->validate([
            'testerEmail' => 'required|email|max:120',
            'testerName' => 'required|string|max:60',
        ]);

        $this->testerEmail = strtolower(trim($this->testerEmail));
        $this->testerName = trim($this->testerName);
        session(['tester_email' => $this->testerEmail, 'tester_name' => $this->testerName]);

        // Remember the tester in the browser too, so their email (the key their saved verdicts
        // hang off) survives the server session expiring — they return straight to their progress.
        $this->dispatch('tester-identity-saved', email: $this->testerEmail, name: $this->testerName);
    }

    /**
     * Re-establish a tester from their browser-remembered identity when the server session has
     * lapsed. The email is the durable key: their verdicts are loaded straight back from it.
     */
    public function restoreIdentity(string $email, string $name = ''): void
    {
        $email = strtolower(trim($email));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $this->testerEmail = $email;
        $this->testerName = trim($name);
        session(['tester_email' => $this->testerEmail, 'tester_name' => $this->testerName]);
        $this->syncNotesToSavedRun();
    }

    public function signOut(): void
    {
        session()->forget(['tester_email', 'tester_name']);
        $this->reset(['testerEmail', 'testerName', 'feature', 'index', 'notes']);
        $this->dispatch('tester-identity-cleared');
    }

    public function goTo(int $i): void
    {
        $total = $this->scenarioCount();
        $this->index = max(0, min($i, $total));
        $this->syncNotesToSavedRun();
    }

    public function next(): void
    {
        $this->goTo($this->index + 1);
    }

    public function prev(): void
    {
        $this->goTo($this->index - 1);
    }

    /**
     * Record (or update) this tester's verdict on the current scenario, then advance.
     */
    public function record(string $result): void
    {
        $feature = TestingCatalog::feature((string) $this->feature);

        if ($feature === null || $this->testerEmail === '' || ! in_array($result, [
            VerificationRun::RESULT_WORKED,
            VerificationRun::RESULT_ISSUE,
            VerificationRun::RESULT_SKIPPED,
        ], true)) {
            return;
        }

        $scenario = $feature['scenarios'][$this->index] ?? null;

        if ($scenario === null) {
            return;
        }

        VerificationRun::updateOrCreate(
            ['tester_email' => $this->testerEmail, 'scenario_id' => $scenario['id']],
            [
                'tester_name' => $this->testerName,
                'feature' => $feature['code'],
                'result' => $result,
                'notes' => trim($this->notes) ?: null,
            ] + $this->fingerprintFor($scenario),
        );

        $this->next();
    }

    /**
     * The version a verdict is being given against: the scenario's Gherkin + implementation
     * fingerprints and the current commit, so we can later tell it changed and explain how. A
     * fresh verdict clears any pending "please re-test" notice, since it is now up to date.
     *
     * @param  array<string, mixed>  $scenario
     * @return array{spec_hash: ?string, impl_hash: ?string, verified_commit: ?string, change_notified_hash: null}
     */
    private function fingerprintFor(array $scenario): array
    {
        $fingerprint = app(ScenarioFingerprint::class);

        return [
            'spec_hash' => $fingerprint->specHash($scenario['id']),
            'impl_hash' => $fingerprint->implHash(TestingCatalog::sources($scenario['id'])),
            'verified_commit' => app(ScenarioChangeLog::class)->currentCommit(),
            'change_notified_hash' => null,
        ];
    }

    /**
     * Defer a delayed-feedback scenario: record a `waiting` check and compute when the
     * notification is next expected in the tester's timezone. A scheduled sweep then emails
     * them just before that moment to watch for it, and again after to record the outcome.
     */
    public function defer(): void
    {
        $feature = TestingCatalog::feature((string) $this->feature);

        if ($feature === null || $this->testerEmail === '') {
            return;
        }

        $scenario = $feature['scenarios'][$this->index] ?? null;

        if ($scenario === null || ! TestingCatalog::isDelayed($scenario)) {
            return;
        }

        $expectedAt = ExpectedNotificationTime::nextOccurrence(
            $scenario['delayed']['at'],
            $this->testerTimezone,
            VerificationRun::FIRST_REMINDER_LEAD_MINUTES,
        );

        VerificationRun::updateOrCreate(
            ['tester_email' => $this->testerEmail, 'scenario_id' => $scenario['id']],
            [
                'tester_name' => $this->testerName,
                'tester_timezone' => $this->testerTimezone,
                'feature' => $feature['code'],
                'result' => VerificationRun::RESULT_WAITING,
                'notes' => trim($this->notes) ?: null,
                'expected_at' => $expectedAt,
                'first_reminder_sent_at' => null,
                'second_reminder_sent_at' => null,
            ] + $this->fingerprintFor($scenario),
        );

        $this->next();
    }

    private function scenarioCount(): int
    {
        $feature = TestingCatalog::feature((string) $this->feature);

        return $feature ? count($feature['scenarios']) : 0;
    }

    /**
     * When landing on a scenario, show whatever this tester last wrote for it (so going
     * back shows their own prior note rather than a blank box).
     */
    private function syncNotesToSavedRun(): void
    {
        $this->notes = (string) ($this->savedRun()?->notes ?? '');
    }

    private function savedRun(): ?VerificationRun
    {
        $feature = TestingCatalog::feature((string) $this->feature);
        $scenario = $feature['scenarios'][$this->index] ?? null;

        if ($scenario === null || $this->testerEmail === '') {
            return null;
        }

        return VerificationRun::where('tester_email', $this->testerEmail)
            ->where('scenario_id', $scenario['id'])
            ->first();
    }

    public function render()
    {
        $features = TestingCatalog::features();
        $activeFeature = $this->feature ? ($features[$this->feature] ?? null) : null;

        // This tester's runs, and their verdicts keyed by scenario id.
        $runs = $this->testerEmail !== ''
            ? VerificationRun::where('tester_email', $this->testerEmail)->get()
            : collect();
        $verdicts = $runs->pluck('result', 'scenario_id')->all();

        // Scenarios whose Gherkin or implementation changed since this tester answered — they
        // should re-test. Keyed by scenario id, each value says which side(s) moved.
        $stale = [];
        $fingerprint = app(ScenarioFingerprint::class);
        foreach ($runs as $run) {
            if (! in_array($run->result, [VerificationRun::RESULT_WORKED, VerificationRun::RESULT_ISSUE, VerificationRun::RESULT_SKIPPED], true)) {
                continue;
            }

            $specNow = $fingerprint->specHash($run->scenario_id);
            $implNow = $fingerprint->implHash(TestingCatalog::sources($run->scenario_id));
            $specChanged = $run->spec_hash !== null && $specNow !== null && $run->spec_hash !== $specNow;
            $implChanged = $run->impl_hash !== null && $implNow !== null && $run->impl_hash !== $implNow;

            if ($specChanged || $implChanged) {
                $stale[$run->scenario_id] = ['spec' => $specChanged, 'impl' => $implChanged];
            }
        }
        $staleFeatures = collect($stale)->keys()
            ->map(fn ($id) => TestingCatalog::scenario($id)['feature'] ?? null)
            ->filter()->flip();

        // Per-feature completion count for the chooser pills. A still-waiting deferred check is
        // not counted as done — it's pending, and surfaces separately in the "waiting on" list.
        $done = [];
        foreach ($features as $code => $f) {
            $done[$code] = collect($f['scenarios'])
                ->filter(fn ($s) => isset($verdicts[$s['id']]) && $verdicts[$s['id']] !== VerificationRun::RESULT_WAITING)
                ->count();
        }

        $scenarios = $activeFeature['scenarios'] ?? [];
        $current = $scenarios[$this->index] ?? null;

        // Device features install the real app instead of embedding a preview — build the
        // install QR for the app this feature uses.
        $isDevice = $activeFeature !== null && TestingCatalog::isDevice($activeFeature);
        $apkUrl = $isDevice ? config('user_testing.apps.'.$activeFeature['device']['app']) : null;

        // A physical device can't be auto-logged-in like the embedded web preview, so show the
        // tester a real is_test login to type into the installed app. Gated the same way as the
        // auto-login: only surfaced where impersonation is enabled (i.e. not bare production).
        $deviceLogin = ($isDevice && config('user_testing.impersonation'))
            ? app(ScenarioFixtures::class)->deviceLogin($activeFeature['device']['app'])
            : null;

        // Delayed-feedback scenarios (a notification that fires later) can be deferred with an
        // email reminder instead of forcing the tester to wait in front of the screen.
        $isDelayed = $current !== null && TestingCatalog::isDelayed($current);

        // Checks this tester is waiting on — shown on the chooser so pending work stays visible.
        $waiting = ($this->testerEmail !== '')
            ? VerificationRun::where('tester_email', $this->testerEmail)
                ->where('result', VerificationRun::RESULT_WAITING)
                ->orderBy('expected_at')
                ->get()
            : collect();

        return view('livewire.user-testing', [
            'features' => $features,
            'groupedFeatures' => TestingCatalog::byGroup(),
            'activeFeature' => $activeFeature,
            'current' => $current,
            'total' => count($scenarios),
            'done' => $done,
            'verdicts' => $verdicts,
            'currentVerdict' => $current ? ($verdicts[$current['id']] ?? null) : null,
            'isDevice' => $isDevice,
            'isDelayed' => $isDelayed,
            'apkUrl' => $apkUrl,
            'deviceLogin' => $deviceLogin,
            'qr' => $apkUrl ? $this->qrDataUri($apkUrl) : null,
            'waiting' => $waiting,
            // Feature codes with at least one deferred check still waiting — the chooser pill
            // shows these as "waiting" (amber), distinct from done (green) and in-progress.
            'waitingFeatures' => $waiting->pluck('feature')->flip(),
            // Scenarios that changed since the tester answered (indigo "re-test"), and which of
            // their features carry at least one.
            'stale' => $stale,
            'staleFeatures' => $staleFeatures,
            'currentStale' => $current ? ($stale[$current['id']] ?? null) : null,
        ]);
    }

    /**
     * A scannable QR code (SVG data URI) for an install URL, rendered server-side.
     */
    private function qrDataUri(string $url): string
    {
        $options = new QROptions([
            'version' => 7,
            'eccLevel' => EccLevel::L,
            'scale' => 5,
        ]);

        return (new QRCode($options))->render($url);
    }
}
