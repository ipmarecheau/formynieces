<?php

declare(strict_types=1);

namespace App\Services\QuestionBank;

/**
 * The outcome of a Moodle import run — the same shape whether it was a dry run
 * (preview) or a committed import, so the admin sees exactly what did or would
 * happen before and after pressing the button.
 */
final class ImportReport
{
    public bool $dryRun = true;

    public int $parsed = 0;

    public int $created = 0;

    public int $updated = 0;

    public int $imagesStored = 0;

    /** @var list<array{ref:string, reason:string}> */
    public array $skipped = [];

    /** @var array<int, int> module_id => question count that landed on it */
    public array $byModule = [];

    public function skip(string $ref, string $reason): void
    {
        $this->skipped[] = ['ref' => $ref, 'reason' => $reason];
    }

    public function skippedCount(): int
    {
        return count($this->skipped);
    }

    public function summary(): string
    {
        $verb = $this->dryRun ? 'would be' : 'were';

        return "{$this->parsed} questions parsed · {$this->created} {$verb} created · "
            ."{$this->updated} {$verb} updated · {$this->skippedCount()} skipped · "
            ."{$this->imagesStored} images stored";
    }
}
