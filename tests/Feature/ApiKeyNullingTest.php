<?php

use Hristijans\AiStager\AiStagerServiceProvider;
use Hristijans\AiStager\Tests\StagerEnabledTestCase;
use Hristijans\AiStager\Tests\TestCase;

// Split into two files because Pest v4 requires one TestCase per file.
// This file covers the "stager enabled" scenarios.
uses(StagerEnabledTestCase::class);

it('nulls openai api key when stager is enabled', function () {
    expect(config('ai.providers.openai.key'))->toBe('ai-stager-disabled');
});

it('nulls all key field variants', function () {
    // Configure a provider with all key field variants before boot (they get
    // set in StagerEnabledTestCase::getEnvironmentSetUp). We add a second
    // provider here and re-run nullProviderKeys to verify each field type.
    config([
        'ai.providers.custom' => [
            'driver' => 'custom',
            'key' => 'my-key',
            'secret' => 'my-secret',
            'token' => 'my-token',
            'api_key' => 'my-api-key',
        ],
    ]);

    // Manually invoke the trait method to null the newly added provider
    $provider = new class extends AiStagerServiceProvider
    {
        public function __construct()
        {
            // Skip parent constructor — we only need nullProviderKeys()
        }

        public function callNullProviderKeys(): void
        {
            $this->nullProviderKeys();
        }
    };

    $provider->callNullProviderKeys();

    expect(config('ai.providers.custom.key'))->toBe('ai-stager-disabled');
    expect(config('ai.providers.custom.secret'))->toBe('ai-stager-disabled');
    expect(config('ai.providers.custom.token'))->toBe('ai-stager-disabled');
    expect(config('ai.providers.custom.api_key'))->toBe('ai-stager-disabled');
});
