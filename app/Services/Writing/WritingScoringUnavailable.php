<?php

declare(strict_types=1);

namespace App\Services\Writing;

use RuntimeException;

/**
 * Thrown when the AI scoring provider is rate-limited or unavailable, so the caller
 * can save the submission and queue it for scoring rather than showing a broken or
 * zeroed rubric (WR-03).
 */
final class WritingScoringUnavailable extends RuntimeException {}
