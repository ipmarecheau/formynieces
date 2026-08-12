<?php

declare(strict_types=1);

namespace App\Services\Safety;

/**
 * The verdict from the child-safety moderator (AG-12..15). Any non-safe result means the
 * content must be withheld; `concerning` marks a category that also escalates to an adult.
 */
class SafetyResult
{
    private function __construct(
        public readonly bool $safe,
        public readonly ?string $category = null,
        public readonly bool $concerning = false,
    ) {}

    public static function safe(): self
    {
        return new self(true);
    }

    public static function unsafe(?string $category, bool $concerning): self
    {
        return new self(false, $category, $concerning);
    }

    /** Fail-closed: the classifier was unavailable, so the content is withheld (AG-14). */
    public static function blocked(): self
    {
        return new self(false);
    }
}
