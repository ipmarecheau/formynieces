<?php

namespace App\Models;

use Database\Factories\SupportTicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One in-app help message — typed or a voice note — raised by a user at the moment
 * they hit friction. Triaged in the admin panel to catch problems early.
 */
class SupportTicket extends Model
{
    /** @use HasFactory<SupportTicketFactory> */
    use HasFactory;

    public const STATUS_OPEN = 'open';

    public const STATUS_TRIAGED = 'triaged';

    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'user_id',
        'role',
        'student_id',
        'kind',
        'message',
        'audio_path',
        'transcript',
        'url',
        'user_agent',
        'context',
        'status',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
