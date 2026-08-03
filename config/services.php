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
        'title' => env('LLM_TITLE', 'ForMyNieces'),
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
