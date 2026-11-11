<?php

namespace App\Models;

use Database\Factories\PushDeliveryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An audit row for one push attempt — the admin panel's source of truth.
 *
 * @see formynieces-spec/features/push_notifications.feature (PN-08)
 */
class PushDelivery extends Model
{
    /** @use HasFactory<PushDeliveryFactory> */
    use HasFactory;

    public const OUTCOME_DELIVERED = 'delivered';

    public const OUTCOME_SUPPRESSED = 'suppressed';

    public const OUTCOME_FAILED = 'failed';

    public const OUTCOME_RETRYABLE = 'retryable';

    protected $fillable = [
        'user_id',
        'type',
        'criticality',
        'platform',
        'outcome',
        'reason',
        'detail',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
