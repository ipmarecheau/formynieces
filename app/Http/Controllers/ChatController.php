<?php

namespace App\Http\Controllers;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * LC-02/04 — the Smooth chat widget's two endpoints: start a conversation,
 * and append a visitor message (which notifies the team via Slack + email).
 */
class ChatController extends Controller
{
    /** POST /chat/session — the visitor's first turn opens a conversation. */
    public function start(): JsonResponse
    {
        $conversation = ChatConversation::create(['last_message_at' => now()]);

        ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'role' => 'bot',
            'body' => 'Ahoy! I’m Smooth. Have a question about the voyage for your child?',
        ]);

        return response()->json(['id' => $conversation->id]);
    }

    /**
     * POST /chat/message — append a visitor message, refresh the captured
     * qualification fields, and notify the team instantly (LC-04).
     */
    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:chat_conversations'],
            'body' => ['required', 'string', 'max:2000'],
            'visitor_name' => ['nullable', 'string', 'max:120'],
            'contact' => ['nullable', 'string', 'max:190'],
            'child_standard' => ['nullable', 'in:Standard 3,Standard 4,Standard 5,Not sure yet'],
            'worry' => ['nullable', 'string', 'max:1000'],
        ]);

        $conversation = ChatConversation::findOrFail($validated['id']);

        ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'role' => 'visitor',
            'body' => $validated['body'],
        ]);

        foreach (['visitor_name', 'contact', 'child_standard', 'worry'] as $field) {
            if (! empty($validated[$field])) {
                $conversation->{$field} = $validated[$field];
            }
        }
        $conversation->last_message_at = now();
        $conversation->save();

        $this->notifyTeam($conversation);

        return response()->json(['ok' => true]);
    }

    /**
     * Slack webhook + email, each wrapped so a notification failure never
     * breaks the visitor's chat (LC-04).
     */
    private function notifyTeam(ChatConversation $conversation): void
    {
        $summary = sprintf(
            '💬 %s · %s · worry: %s · contact: %s',
            $conversation->visitor_name ?: 'A parent',
            $conversation->child_standard ?: 'standard unknown',
            $conversation->worry ? Str::limit($conversation->worry, 80) : '—',
            $conversation->contact ?: 'not given',
        );
        $lastVisitorLine = $conversation->messages()
            ->where('role', 'visitor')
            ->latest('id')
            ->value('body');

        $webhook = config('services.slack.chat_webhook');
        if ($webhook) {
            try {
                Http::post($webhook, ['text' => $summary."\n> ".Str::limit((string) $lastVisitorLine, 300)]);
            } catch (\Throwable $e) {
                Log::warning('Chat Slack notify failed: '.$e->getMessage());
            }
        }

        try {
            Mail::raw(
                $summary."\n\nLast message: ".$lastVisitorLine."\n\nOpen the admin panel → Chat Conversations to reply.",
                function ($message) use ($summary) {
                    $message->to(config('services.chat.notify_email'))
                        ->subject('SmoothSeas chat — '.$summary);
                }
            );
        } catch (\Throwable $e) {
            Log::warning('Chat email notify failed: '.$e->getMessage());
        }
    }
}
