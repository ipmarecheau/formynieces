<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** LC-02 — a single line in a chat transcript, from the bot or the visitor. */
class ChatMessage extends Model
{
    protected $fillable = ['chat_conversation_id', 'role', 'body'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class);
    }
}
