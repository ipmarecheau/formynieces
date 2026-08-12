<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Provider-agnostic LLM client over any OpenAI-compatible chat API (OpenRouter,
 * DashScope, Groq, a self-hosted vLLM, …). The provider, model, and key are all
 * config-driven (`config/services.llm`), so switching providers is a config change,
 * never a code change. Callers use complete()/completeJson() and never care who is
 * behind them.
 */
class LlmService
{
    private string $apiKey;

    private string $model;

    private string $baseUrl;

    /** Optional attribution headers some providers (e.g. OpenRouter) surface. */
    private array $extraHeaders;

    public function __construct(private LlmBudget $budget)
    {
        // Cast through '' so a missing key never fatals construction — a failed
        // call degrades gracefully via fallback() instead.
        $this->apiKey = (string) config('services.llm.key');
        $this->model = (string) config('services.llm.model');
        $this->baseUrl = rtrim((string) config('services.llm.base_url'), '/');

        $this->extraHeaders = array_filter([
            'HTTP-Referer' => config('services.llm.referer'),
            'X-Title' => config('services.llm.title'),
        ]);
    }

    /**
     * A single-turn completion. When $studentId is given, the call's real token usage
     * is metered to her monthly budget ledger (AG-01).
     */
    public function complete(string $systemPrompt, string $userPrompt, int $maxTokens = 1024, ?int $studentId = null): string
    {
        return $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ], $maxTokens, $studentId);
    }

    /**
     * A multi-turn chat completion — the surface the clarify chat and re-teach use.
     * $messages is an OpenAI-style role/content list. Usage is metered to $studentId.
     *
     * @param  array<int, array{role:string, content:string}>  $messages
     */
    public function chat(array $messages, int $maxTokens = 512, ?int $studentId = null): string
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->withHeaders($this->extraHeaders)
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'max_tokens' => $maxTokens,
                    'messages' => $messages,
                ]);

            if ($response->failed()) {
                Log::error('LLM API error', ['status' => $response->status(), 'body' => $response->body()]);

                return $this->fallback();
            }

            $this->meter($studentId, $response->json('usage'));

            return $response->json('choices.0.message.content') ?? $this->fallback();

        } catch (\Exception $e) {
            Log::error('LLM API exception', ['message' => $e->getMessage()]);

            return $this->fallback();
        }
    }

    /**
     * Record one call's real usage against a student's monthly budget. Uses the
     * provider's usage.cost when present (OpenRouter), else estimates from tokens.
     *
     * @param  array<string, mixed>|null  $usage
     */
    private function meter(?int $studentId, ?array $usage): void
    {
        if ($studentId === null || $usage === null) {
            return;
        }

        $this->budget->record(
            $studentId,
            (int) ($usage['prompt_tokens'] ?? 0),
            (int) ($usage['completion_tokens'] ?? 0),
            isset($usage['cost']) ? (float) $usage['cost'] : null,
        );
    }

    public function completeJson(string $systemPrompt, string $userPrompt, int $maxTokens = 1024): array
    {
        $system = $systemPrompt."\n\nYou must respond with valid JSON only. No preamble, no markdown, no backticks.";

        $raw = $this->complete($system, $userPrompt, $maxTokens);

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('LLM JSON parse error', ['raw' => $raw]);

            return [];
        }
    }

    private function fallback(): string
    {
        return "I'm unable to generate a response right now. Please try again shortly.";
    }
}
