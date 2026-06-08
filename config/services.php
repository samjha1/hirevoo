<?php

return [

    'openai' => [
        // Prefer legacy/project-specific key name if present (some installs store it in lowercase).
        // Fallback to the standard OPENAI_API_KEY.
        'key' => env('openai_api_key_main', env('OPENAI_API_KEY')),
        'model' => env('OPENAI_MODEL', 'gpt-5-mini'),
    ],

    /*
     * OpenRouter (OpenAI-compatible). Used after direct OpenAI when keys are set.
     *
     * Order: OPENROUTER_MODEL (e.g. openai/gpt-4o-mini) → OPENROUTER_MODEL_FALLBACK (e.g. openai/gpt-oss-20b:free).
     * See https://openrouter.ai/models
     *
     * Optional: OPENROUTER_SKIP_FREE_WHEN_OPENAI=true skips ":free" fallback when OpenAI is set.
     */
    'primary_llm' => [
        'key' => env('OPENROUTER_API_KEY'),
        'base_url' => rtrim(env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'), '/'),
        'model' => env('OPENROUTER_MODEL', 'openai/gpt-4o-mini'),
        'http_referer' => env('OPENROUTER_HTTP_REFERER', env('APP_URL', 'http://localhost')),
        'app_title' => env('OPENROUTER_APP_TITLE', env('APP_NAME', 'Hirevo')),
        // Free OpenRouter models often 429 upstream; exponential backoff retries reduce flaky UI calls.
        'retry_on_429' => (bool) env('OPENROUTER_RETRY_ON_429', true),
        // Max HTTP attempts when OpenRouter returns 429 (each attempt waits longer: ~1s, 2s, 4s, …).
        'openrouter_429_max_attempts' => max(1, min(8, (int) env('OPENROUTER_429_MAX_ATTEMPTS', 4))),
        // Max seconds between 429 retries (lower = faster failures; higher = more patience on free tier).
        'openrouter_429_delay_cap_seconds' => max(1, min(15, (int) env('OPENROUTER_429_DELAY_CAP', 4))),
        // Last resort on OpenRouter after OPENROUTER_MODEL fails (e.g. openai/gpt-oss-20b:free). Empty = skip.
        'model_fallback' => env('OPENROUTER_MODEL_FALLBACK', 'openai/gpt-oss-20b:free'),
        // After first 429, skip OpenRouter for remaining calls in the same request (request attributes).
        'circuit_on_429' => (bool) env('OPENROUTER_CIRCUIT_ON_429', true),
        // Global cache skip (default off): when true, one 429 blocks OpenRouter for all users for circuit_cache_ttl seconds.
        'circuit_cache' => (bool) env('OPENROUTER_CIRCUIT_CACHE', false),
        'circuit_cache_ttl' => (int) env('OPENROUTER_CIRCUIT_CACHE_TTL', 120),
        // false = always try OpenRouter first; true = for ":free" models, use OpenAI directly if both keys set.
        'skip_free_when_openai' => (bool) env('OPENROUTER_SKIP_FREE_WHEN_OPENAI', false),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'azure' => [
        'client_id' => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect' => env('AZURE_REDIRECT_URI', env('APP_URL') . '/auth/microsoft/callback'),
        'tenant' => env('AZURE_TENANT_ID', 'common'),
    ],

];
