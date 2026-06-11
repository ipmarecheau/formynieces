<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://api.groq.com/openai/v1';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
        $this->model  = config('services.groq.model');
    }

    public function complete(string $systemPrompt, string $userPrompt, int $maxTokens = 1024): string
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model'       => $this->model,
                    'max_tokens'  => $maxTokens,
                    'messages'    => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user',   'content' => $userPrompt],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Groq API error', ['status' => $response->status(), 'body' => $response->body()]);
                return $this->fallback();
            }

            return $response->json('choices.0.message.content') ?? $this->fallback();

        } catch (\Exception $e) {
            Log::error('Groq API exception', ['message' => $e->getMessage()]);
            return $this->fallback();
        }
    }

    public function completeJson(string $systemPrompt, string $userPrompt, int $maxTokens = 1024): array
    {
        $system = $systemPrompt . "\n\nYou must respond with valid JSON only. No preamble, no markdown, no backticks.";

        $raw = $this->complete($system, $userPrompt, $maxTokens);

        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::error('Groq JSON parse error', ['raw' => $raw]);
            return [];
        }
    }

    private function fallback(): string
    {
        return "I'm unable to generate a response right now. Please try again shortly.";
    }
}