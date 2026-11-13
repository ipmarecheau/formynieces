<?php

namespace App\Models;

use Database\Factories\VerificationRunFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One tester's verdict on one scenario from a guided /user-testing walkthrough. Evidence
 * for Isaac's review — not a specs:verify record. See the create migration for the rule.
 */
class VerificationRun extends Model
{
    /** @use HasFactory<VerificationRunFactory> */
    use HasFactory;

    public const RESULT_WORKED = 'worked';

    public const RESULT_ISSUE = 'issue';

    public const RESULT_SKIPPED = 'skipped';

    protected $fillable = [
        'tester_email',
        'tester_name',
        'feature',
        'scenario_id',
        'result',
        'notes',
        'screenshot_path',
    ];
}
