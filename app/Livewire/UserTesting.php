<?php

namespace App\Livewire;

use App\Models\VerificationRun;
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

    /** The feature code being walked, or null on the chooser screen. */
    public ?string $feature = null;

    /** Index of the current scenario within the feature. */
    public int $index = 0;

    public string $notes = '';

    public function mount(?string $feature = null): void
    {
        $this->testerEmail = (string) session('tester_email', '');
        $this->testerName = (string) session('tester_name', '');
        $this->feature = $feature ? strtoupper($feature) : null;
        $this->syncNotesToSavedRun();
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
            ],
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

        // This tester's verdicts across all scenarios, keyed by scenario id.
        $verdicts = [];
        if ($this->testerEmail !== '') {
            $verdicts = VerificationRun::where('tester_email', $this->testerEmail)
                ->pluck('result', 'scenario_id')
                ->all();
        }

        // Per-feature completion count for the chooser pills.
        $done = [];
        foreach ($features as $code => $f) {
            $done[$code] = collect($f['scenarios'])
                ->filter(fn ($s) => isset($verdicts[$s['id']]))
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

        return view('livewire.user-testing', [
            'features' => $features,
            'activeFeature' => $activeFeature,
            'current' => $current,
            'total' => count($scenarios),
            'done' => $done,
            'verdicts' => $verdicts,
            'currentVerdict' => $current ? ($verdicts[$current['id']] ?? null) : null,
            'isDevice' => $isDevice,
            'apkUrl' => $apkUrl,
            'deviceLogin' => $deviceLogin,
            'qr' => $apkUrl ? $this->qrDataUri($apkUrl) : null,
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
