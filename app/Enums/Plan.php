<?php

namespace App\Enums;

/**
 * A billing account's subscription plan.
 *
 * Free is the permanently-free top-of-funnel tier (the map + mastery quizzes only —
 * see free_tier.feature). Trial is the one-month free run of the full product that a
 * lead claims (lead_capture.feature); it grants full access until it lapses, then the
 * account falls back to Free. Premium is a paying subscriber.
 *
 * Access is resolved on the BILLING account — for a student that is their guardian, so
 * a child's access follows the parent who pays.
 */
enum Plan: string
{
    case Free = 'free';
    case Trial = 'trial';
    case Premium = 'premium';

    /** Whether this plan unlocks the full product (lessons, re-teach, rituals, AI, full reporting). */
    public function grantsFullAccess(): bool
    {
        return match ($this) {
            self::Trial, self::Premium => true,
            self::Free => false,
        };
    }

    /** Resolve a stored value safely, defaulting unknown/null to Free. */
    public static function fromValue(?string $value): self
    {
        return $value !== null ? (self::tryFrom($value) ?? self::Free) : self::Free;
    }
}
