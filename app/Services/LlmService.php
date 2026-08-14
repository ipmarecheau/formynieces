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

    /** @var array<int, string> Other models OpenRouter falls back to if the primary provider errors. */
    private array $fallbackModels;

    /** @var array<int, string> Preferred provider order for the primary model. */
    private array $providerOrder;

    public function __construct(private LlmBudget $budget)
    {
        // Cast through '' so a missing key never fatals construction — a failed
        // call degrades gracefully via fallback() instead.
        $this->apiKey = (string) config('services.llm.key');
        $this->model = (string) config('services.llm.model');
        $this->baseUrl = rtrim((string) config('services.llm.base_url'), '/');
        $this->fallbackModels = (array) config('services.llm.fallback_models', []);
        $this->providerOrder = (array) config('services.llm.provider_order', []);

        $this->extraHeaders = array_filter([
            'HTTP-Referer' => config('services.llm.referer'),
            'X-Title' => config('services.llm.title'),
        ]);
    }

    /**
     * A single-turn completion. When $studentId is given, the call's real token usage
     * is metered to her monthly budget ledger (AG-01).
     */
    public function complete(string $systemPrompt, string $userPrompt, int $maxTokens = 1024, ?int $studentId = null, bool $essential = false): string
    {
        return $this->chat([
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt],
        ], $maxTokens, $studentId, $essential);
    }

    /**
     * A multi-turn chat completion — the surface the clarify chat and re-teach use.
     * $messages is an OpenAI-style role/content list. Usage is metered to $studentId.
     *
     * @param  array<int, array{role:string, content:string}>  $messages
     */
    public function chat(array $messages, int $maxTokens = 512, ?int $studentId = null, bool $essential = false, ?string $model = null): string
    {
        // Budget is checked BEFORE any request, so spend never overshoots (AG-02..04).
        if ($studentId !== null && ! $this->budget->canSpend($studentId, $essential)) {
            return $this->fallback();
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->withHeaders($this->extraHeaders)
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", $this->payload($messages, $maxTokens, $model));

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
     * The request body, with OpenRouter provider routing + model fallbacks so a rate-limited or
     * down primary provider falls through to another provider (or model) instead of erroring —
     * the fix for the qwen shared-pool 429.
     *
     * @param  array<int, array{role:string, content:string}>  $messages
     * @return array<string, mixed>
     */
    private function payload(array $messages, int $maxTokens, ?string $model): array
    {
        $primary = $model ?? $this->model;

        $payload = [
            'model' => $primary,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
            // Disable hidden reasoning: Qwen3-Flash otherwise spends the whole token budget
            // "thinking" and returns EMPTY content. We want the answer, not the chain-of-thought.
            'reasoning' => ['enabled' => false],
            // Ask OpenRouter to return the call's real charged cost in usage.cost (exact metering).
            'usage' => ['include' => true],
            // Let OpenRouter try another provider for this model if the first errors/rate-limits.
            'provider' => ['allow_fallbacks' => true],
        ];

        if ($this->providerOrder !== []) {
            $payload['provider']['order'] = $this->providerOrder;
        }

        // Model-level fallbacks: if the primary model's providers ALL error (e.g. the shared-pool
        // 429), OpenRouter tries these other models next, in order.
        // OpenRouter rejects a `models` array with MORE than 3 items (400 "'models' array must have
        // 3 items or fewer"), which would fail EVERY call. Cap to primary + first 2 fallbacks so a
        // longer configured chain degrades gracefully instead of erroring the whole request.
        if ($this->fallbackModels !== []) {
            $payload['models'] = array_slice(
                array_values(array_unique(array_merge([$primary], $this->fallbackModels))),
                0,
                3,
            );
        }

        return $payload;
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

    public function completeJson(string $systemPrompt, string $userPrompt, int $maxTokens = 1024, ?int $studentId = null, bool $essential = false): array
    {
        $system = $systemPrompt."\n\nYou must respond with valid JSON only. No preamble, no markdown, no backticks.";

        $raw = $this->complete($system, $userPrompt, $maxTokens, $studentId, $essential);

        try {
            return json_decode($this->extractJson($raw), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('LLM JSON parse error', ['raw' => $raw]);

            return [];
        }
    }

    /**
     * Pull the JSON object out of a model reply — some models (Mistral, Gemma, GLM) wrap it in a
     * ```json fence or add a stray sentence around it, which breaks a strict json_decode. Strips a
     * code fence, then falls back to the outermost {...} so a fallback model never breaks grading.
     */
    private function extractJson(string $raw): string
    {
        $text = trim($raw);

        if (str_starts_with($text, '```')) {
            $text = trim((string) preg_replace(['/^```[a-zA-Z]*\s*/', '/\s*```$/'], '', $text));
        }

        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start !== false && $end !== false && $end > $start) {
            return substr($text, $start, $end - $start + 1);
        }

        return $text;
    }

    private function fallback(): string
    {
        return "I'm unable to generate a response right now. Please try again shortly.";
    }
}
