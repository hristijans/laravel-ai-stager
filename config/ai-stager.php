<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Stager
    |--------------------------------------------------------------------------
    | Set AI_STAGER_ENABLED=true in .env.staging ONLY.
    |
    | When enabled:
    |   1. All AI provider API keys are nulled (replaced with invalid sentinel)
    |   2. The StagerAiManager decorator intercepts all AI calls
    |   3. Fixtures are returned instead of real API responses
    |   4. The stager dashboard is available at /stager
    |
    | NEVER set this to true in production. Real API keys will be invalidated.
    */
    'enabled' => env('AI_STAGER_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Simulated Latency
    |--------------------------------------------------------------------------
    | Milliseconds to sleep before returning a fixture response.
    | Simulates realistic AI response time for demos.
    | Set to 0 to disable. Recommended range: 800–2000ms.
    */
    'latency_ms' => env('AI_STAGER_LATENCY_MS', 1200),

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    | Log every intercepted AI call to the default Laravel log channel.
    | Use this to verify complete interception coverage.
    | Check that every AI-powered feature in your app produces a log entry.
    */
    'log' => env('AI_STAGER_LOG', false),

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    'dashboard' => [
        'enabled'    => env('AI_STAGER_DASHBOARD', true),
        'path'       => env('AI_STAGER_DASHBOARD_PATH', 'stager'),
        'middleware' => ['web', 'auth'],

        /*
        |----------------------------------------------------------------------
        | Intercept Log (shown in /stager/logs)
        |----------------------------------------------------------------------
        | log_ttl  — seconds to keep entries in the cache before they expire.
        | log_max  — maximum number of entries retained (oldest are pruned).
        */
        'log_ttl' => env('AI_STAGER_LOG_TTL', 3600),
        'log_max' => env('AI_STAGER_LOG_MAX', 200),
    ],

    /*
    |--------------------------------------------------------------------------
    | Agent Fixtures
    |--------------------------------------------------------------------------
    | Map agent class names to fixture configurations.
    |
    | Strategies:
    |   'default'  — same fixture every call
    |   'sequence' — cycles through fixtures in order, loops at end
    |   'match'    — keyword match on prompt text, falls back to 'default'
    |
    | Fixture values:
    |   File path  — relative to resources/ai-fixtures/ (e.g. 'agents/my/response.txt')
    |   Inline     — any string that is not an existing file path
    |
    | The '*' catch-all is required. It handles any agent not explicitly mapped.
    */
    'agents' => [
        '*' => [
            'strategy' => 'default',
            'default'  => 'Simulated AI response for staging.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Embedding Strategy
    |--------------------------------------------------------------------------
    | hash — deterministic, unique vector per input. Similarity search works.
    | zero — all-zeros vector. Fast but no meaningful similarity.
    |
    | dimensions must match your real embedding model:
    |   text-embedding-3-small → 1536
    |   text-embedding-3-large → 3072
    |   text-embedding-ada-002 → 1536
    |   Gemini embedding-001   → 768
    */
    'embeddings' => [
        'strategy'   => env('AI_STAGER_EMBEDDING_STRATEGY', 'hash'),
        'dimensions' => env('AI_STAGER_EMBEDDING_DIMENSIONS', 1536),
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Fixtures
    |--------------------------------------------------------------------------
    | Path relative to resources/ai-fixtures/
    | The package ships a default placeholder at resources/fixtures/images/placeholder.png
    */
    'images' => [
        'default' => env('AI_STAGER_IMAGE_FIXTURE', 'images/placeholder.png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Audio Fixtures
    |--------------------------------------------------------------------------
    */
    'audio' => [
        'default' => env('AI_STAGER_AUDIO_FIXTURE', 'audio/placeholder.mp3'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transcription Fixtures
    |--------------------------------------------------------------------------
    */
    'transcription' => [
        'default' => env('AI_STAGER_TRANSCRIPTION_DEFAULT',
            'This is a simulated transcription for the staging environment.'),
    ],

];
