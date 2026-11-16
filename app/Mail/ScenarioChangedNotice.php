<?php

namespace App\Mail;

use App\Models\VerificationRun;
use App\Services\UserTesting\TestingCatalog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a tester that a scenario they gave a verdict on has changed since — its steps (the BDD
 * definition), its implementation, or both — and invites a re-test, explaining what moved with
 * the implementation commit subjects pulled from git.
 *
 * @phpstan-param array{spec: bool, impl: bool, commits: array<int, string>} $summary
 */
class ScenarioChangedNotice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{spec: bool, impl: bool, commits: array<int, string>}  $summary
     */
    public function __construct(
        public VerificationRun $run,
        public array $summary,
    ) {}

    public function envelope(): Envelope
    {
        $scenario = TestingCatalog::scenario($this->run->scenario_id);
        $title = $scenario['title'] ?? $this->run->scenario_id;

        return new Envelope(subject: "Updated — worth another look: {$title}");
    }

    public function content(): Content
    {
        $scenario = TestingCatalog::scenario($this->run->scenario_id);

        return new Content(
            markdown: 'emails.user-testing.scenario-changed',
            with: [
                'name' => $this->run->tester_name,
                'scenarioTitle' => $scenario['title'] ?? $this->run->scenario_id,
                'specChanged' => $this->summary['spec'],
                'implChanged' => $this->summary['impl'],
                'commits' => $this->summary['commits'],
                'lastResult' => $this->run->result,
                'retestUrl' => route('user-testing.feature', strtolower($this->run->feature))
                    .'?scenario='.$this->run->scenario_id,
            ],
        );
    }
}
