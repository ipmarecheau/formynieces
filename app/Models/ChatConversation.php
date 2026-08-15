<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** LC-02/03 — one chat conversation captured by the Smooth widget on a public page. */
class ChatConversation extends Model
{
    public const STATUSES = ['open', 'closed'];

    protected $fillable = [
        'visitor_name',
        'contact',
        'child_standard',
        'worry',
        'status',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
        ];
    }

    /** @return HasMany<ChatMessage> */
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }
}
