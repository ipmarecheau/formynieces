<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * LearningEvent — the scenario-tagged event bus (QC-01).
 *
 * One call at a spec-critical step records a structured, greppable event keyed to the Gherkin
 * scenario it realises (e.g. "LL-14"). The `learning` log channel is the always-on sink used both
 * to DETECT deviation from the BDD spec in production (an expected `Then` that never fires, or fires
 * without its precondition) and to FEED behavioural analytics.
 *
 * The behavioural-analytics forward (PostHog) is added in a later step and is a guarded no-op until a
 * provider is configured; recording an event must never throw into or slow the learning flow.
 */
class LearningEvent
{
    /**
     * Record one spec-critical event.
     *
     * @param  string  $scenario  the Gherkin scenario id this step realises (e.g. "LL-14")
     * @param  string  $event  a dotted event name (e.g. "reteach.started")
     * @param  array<string, mixed>  $props  domain context — IDs and enums only, never child PII
     */
    public static function record(string $scenario, string $event, array $props = [], ?int $studentId = null): void
    {
        Log::channel('learning')->info($event, [
            'scenario' => $scenario,
            'event' => $event,
            'student_id' => $studentId,
            'props' => $props,
        ]);

        // Behavioural-analytics sink (PostHog) is wired in QC-04; guarded no-op until then.
    }
}
