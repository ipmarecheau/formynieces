<?php

namespace App\Services\Admin;

use Carbon\CarbonInterface;

/**
 * One entry in a student's activity timeline (AL-01). Composed from existing records — no
 * new event capture — so the admin can see a child's journey and catch problems early.
 */
final class ActivityEvent
{
    public function __construct(
        public readonly CarbonInterface $at,
        public readonly string $type,
        public readonly string $summary,
        public readonly string $tone = 'neutral', // positive | neutral | negative
    ) {}
}
