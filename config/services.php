<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    // Provider-agnostic LLM client (see App\Services\LlmService). Any
    // OpenAI-compatible chat API — default is OpenRouter serving a Qwen model.
    'llm' => [
        'key' => env('LLM_API_KEY'),
        'model' => env('LLM_MODEL', 'qwen/qwen-2.5-72b-instruct'),
        'base_url' => env('LLM_BASE_URL', 'https://openrouter.ai/api/v1'),
        'referer' => env('LLM_REFERER'), // optional OpenRouter attribution
        'title' => env('LLM_TITLE', 'SmoothSeas'),

        // AI governance budget (AG-01..04). Per-student, per-month, in USD.
        // Discretionary AI (clarify chat, re-teach, worked examples) stops at the soft
        // cap; essential AI (essay grading, guardian summaries) runs to the hard cap.
        'monthly_cap_usd' => (float) env('LLM_MONTHLY_CAP_USD', 1.50),
        'monthly_soft_cap_usd' => (float) env('LLM_MONTHLY_SOFT_CAP_USD', 1.00),
        // Cost fallback when the provider does not return usage.cost — USD per 1M tokens.
        'price_input_per_mtok' => (float) env('LLM_PRICE_INPUT_PER_MTOK', 0.30),
        'price_output_per_mtok' => (float) env('LLM_PRICE_OUTPUT_PER_MTOK', 0.90),
    ],

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
