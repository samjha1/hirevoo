<?php

return [

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    /*
     * OpenRouter (OpenAI-compatible). Used after Bedrock + direct OpenAI when keys are set.
     *
     * Default model: Qwen 3.6 Plus (free). Override with OPENROUTER_MODEL — confirm the exact id on
     * https://openrouter.ai/models (e.g. paid Gemma 4: google/gemma-4-31b-it or similar slug shown there).
     *
     * Optional: OPENROUTER_SKIP_FREE_WHEN_OPENAI=true skips ":free" OpenRouter when OpenAI is set.
     */
    'primary_llm' => [
        'key' => env('OPENROUTER_API_KEY'),
        'base_url' => rtrim(env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'), '/'),
        'model' => env('OPENROUTER_MODEL', 'qwen/qwen3.6-plus:free'),
        'http_referer' => env('OPENROUTER_HTTP_REFERER', env('APP_URL', 'http://localhost')),
        'app_title' => env('OPENROUTER_APP_TITLE', env('APP_NAME', 'Hirevo')),
        // Free OpenRouter models can 429 upstream; retry adds ~2s. Enable only if you want one retry.
        'retry_on_429' => (bool) env('OPENROUTER_RETRY_ON_429', false),
        // After first 429, skip OpenRouter for remaining calls in the same request (request attributes).
        'circuit_on_429' => (bool) env('OPENROUTER_CIRCUIT_ON_429', true),
        // Also remember 429 in cache so parallel/sub-requests and missing request() still skip OpenRouter briefly.
        'circuit_cache' => (bool) env('OPENROUTER_CIRCUIT_CACHE', true),
        'circuit_cache_ttl' => (int) env('OPENROUTER_CIRCUIT_CACHE_TTL', 120),
        // false = always try OpenRouter first; true = for ":free" models, use OpenAI directly if both keys set.
        'skip_free_when_openai' => (bool) env('OPENROUTER_SKIP_FREE_WHEN_OPENAI', false),
    ],

    /*
     * Amazon Bedrock — production: IAM (SigV4) via AWS SDK. Set AWS_ACCESS_KEY_ID + AWS_SECRET_ACCESS_KEY or use the
     * default credential chain (~/.aws/credentials, env, EC2/ECS role). IAM needs bedrock:Converse and bedrock:InvokeModel.
     *
     * Optional legacy: AWS_BEARER_TOKEN_BEDROCK only if BEDROCK_USE_IAM=false (API keys can expire / 403).
     * Model id for on-demand IAM is typically without the us. prefix, e.g. amazon.nova-2-lite-v1:0.
     * Order: Bedrock (if try_first) → OpenAI → OpenRouter → Bedrock again if try_first is false.
     */
    'bedrock' => [
        'use_iam' => filter_var(env('BEDROCK_USE_IAM', true), FILTER_VALIDATE_BOOLEAN),
        // When true, Bedrock is considered configured without .env keys (SDK uses ~/.aws/credentials, IAM role, etc.).
        'allow_default_credential_chain' => filter_var(env('BEDROCK_ALLOW_DEFAULT_CREDENTIAL_CHAIN', false), FILTER_VALIDATE_BOOLEAN),
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'bearer_token' => env('AWS_BEARER_TOKEN_BEDROCK'),
        'region' => env('AWS_BEDROCK_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
        'model_id' => env('BEDROCK_MODEL_ID', 'amazon.nova-2-lite-v1:0'),
        'model_id_fallback' => env('BEDROCK_MODEL_ID_FALLBACK'),
        'try_first' => filter_var(env('LLM_BEDROCK_FIRST', true), FILTER_VALIDATE_BOOLEAN),
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
