<?php

use Hristijans\AiStager\Responses\StagerAgentResponse;
use Hristijans\AiStager\StagerDriver;
use Hristijans\AiStager\Tests\StagerEnabledTestCase;
use Hristijans\AiStager\Tests\Support\FakeAgent;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Contracts\Files\TranscribableAudio;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AudioResponse;
use Laravel\Ai\Responses\EmbeddingsResponse;
use Laravel\Ai\Responses\ImageResponse;
use Laravel\Ai\Responses\RerankingResponse;
use Laravel\Ai\Responses\StreamableAgentResponse;
use Laravel\Ai\Responses\TranscriptionResponse;

uses(StagerEnabledTestCase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function driver(): StagerDriver
{
    return app(StagerDriver::class);
}

function agentPrompt(string $text = 'Hello'): AgentPrompt
{
    return new AgentPrompt(
        agent: new FakeAgent,
        prompt: $text,
        attachments: [],
        provider: app(StagerDriver::class),
        model: 'stager',
    );
}

function fakeAudio(): TranscribableAudio
{
    return new class implements TranscribableAudio {
        public function content(): string { return ''; }
        public function mimeType(): ?string { return 'audio/mpeg'; }
        public function __toString(): string { return ''; }
        public function transcription(): never { throw new \BadMethodCallException('Stub.'); }
    };
}

// ---------------------------------------------------------------------------
// TextProvider — prompt()
// ---------------------------------------------------------------------------

it('prompt() returns a StagerAgentResponse', function () {
    expect(driver()->prompt(agentPrompt()))->toBeInstanceOf(StagerAgentResponse::class);
});

it('prompt() text matches the configured fixture', function () {
    config(['ai-stager.agents.*' => ['strategy' => 'default', 'default' => 'Staged reply.']]);

    expect(driver()->prompt(agentPrompt())->text)->toBe('Staged reply.');
});

it('prompt() response has stager provider and model in meta', function () {
    $response = driver()->prompt(agentPrompt());

    expect($response->meta->provider)->toBe('stager');
    expect($response->meta->model)->toBe('stager');
});

it('prompt() supports JSON key access on structured fixture', function () {
    config(['ai-stager.agents.*' => ['strategy' => 'default', 'default' => '{"name":"Alice","score":42}']]);

    $response = driver()->prompt(agentPrompt());

    expect($response->name)->toBe('Alice');
    expect($response->score)->toBe(42);
});

// ---------------------------------------------------------------------------
// TextProvider — stream()
// ---------------------------------------------------------------------------

it('stream() returns a StreamableAgentResponse', function () {
    expect(driver()->stream(agentPrompt()))->toBeInstanceOf(StreamableAgentResponse::class);
});

// ---------------------------------------------------------------------------
// AudioProvider
// ---------------------------------------------------------------------------

it('audio() returns an AudioResponse', function () {
    expect(driver()->audio('Hello world'))->toBeInstanceOf(AudioResponse::class);
});

it('audio() response has stager meta', function () {
    $response = driver()->audio('Hello');

    expect($response->meta->provider)->toBe('stager');
});

// ---------------------------------------------------------------------------
// ImageProvider
// ---------------------------------------------------------------------------

it('image() returns an ImageResponse', function () {
    expect(driver()->image('A red cube'))->toBeInstanceOf(ImageResponse::class);
});

it('image() response contains a base64 image', function () {
    $response = driver()->image('A red cube');

    expect($response->images)->not->toBeEmpty();
    expect($response->images->first()->image)->not->toBeEmpty();
});

// ---------------------------------------------------------------------------
// EmbeddingProvider
// ---------------------------------------------------------------------------

it('embeddings() returns an EmbeddingsResponse', function () {
    expect(driver()->embeddings(['Hello', 'World']))->toBeInstanceOf(EmbeddingsResponse::class);
});

it('embeddings() produces one vector per input', function () {
    $response = driver()->embeddings(['a', 'b', 'c']);

    expect($response->embeddings)->toHaveCount(3);
});

it('embeddings() vectors have the configured number of dimensions', function () {
    config(['ai-stager.embeddings.dimensions' => 768]);

    $response = driver()->embeddings(['hello'], dimensions: 768);

    expect($response->embeddings[0])->toHaveCount(768);
});

it('hash strategy produces non-zero vectors', function () {
    config(['ai-stager.embeddings.strategy' => 'hash']);

    $response = driver()->embeddings(['non-empty input']);
    $nonZero = array_filter($response->embeddings[0], fn ($v) => $v !== 0.0);

    expect($nonZero)->not->toBeEmpty();
});

it('hash strategy produces identical vectors for the same input', function () {
    config(['ai-stager.embeddings.strategy' => 'hash']);

    $a = driver()->embeddings(['deterministic']);
    $b = driver()->embeddings(['deterministic']);

    expect($a->embeddings[0])->toEqual($b->embeddings[0]);
});

it('zero strategy produces all-zero vectors', function () {
    config(['ai-stager.embeddings.strategy' => 'zero']);

    $response = driver()->embeddings(['any input'], dimensions: 4);
    $allZero = array_filter($response->embeddings[0], fn ($v) => $v !== 0.0);

    expect($allZero)->toBeEmpty();
});

// ---------------------------------------------------------------------------
// RerankingProvider
// ---------------------------------------------------------------------------

it('rerank() returns a RerankingResponse', function () {
    expect(driver()->rerank(['doc a', 'doc b'], 'query'))->toBeInstanceOf(RerankingResponse::class);
});

it('rerank() returns documents with descending scores', function () {
    $response = driver()->rerank(['first', 'second', 'third'], 'q');

    $scores = array_column($response->results, 'score');

    expect($scores[0])->toBeGreaterThan($scores[1]);
    expect($scores[1])->toBeGreaterThan($scores[2]);
});

it('rerank() respects the limit parameter', function () {
    $response = driver()->rerank(['a', 'b', 'c', 'd'], 'q', limit: 2);

    expect($response->results)->toHaveCount(2);
});

// ---------------------------------------------------------------------------
// TranscriptionProvider
// ---------------------------------------------------------------------------

it('transcribe() returns a TranscriptionResponse', function () {
    expect(driver()->transcribe(fakeAudio()))->toBeInstanceOf(TranscriptionResponse::class);
});

it('transcribe() text matches the configured default', function () {
    config(['ai-stager.transcription.default' => 'Custom transcription.']);

    expect(driver()->transcribe(fakeAudio())->text)->toBe('Custom transcription.');
});

// ---------------------------------------------------------------------------
// Latency simulation
// ---------------------------------------------------------------------------

it('does not sleep when latency_ms is 0', function () {
    config(['ai-stager.latency_ms' => 0]);

    $start = microtime(true);
    driver()->prompt(agentPrompt());
    $elapsed = (microtime(true) - $start) * 1000;

    expect($elapsed)->toBeLessThan(100);
});

it('sleeps for approximately the configured latency', function () {
    config(['ai-stager.latency_ms' => 50]);

    $start = microtime(true);
    driver()->audio('test');
    $elapsed = (microtime(true) - $start) * 1000;

    expect($elapsed)->toBeGreaterThanOrEqual(40);
});

// ---------------------------------------------------------------------------
// Logging
// ---------------------------------------------------------------------------

it('logs interceptions when log is enabled', function () {
    config(['ai-stager.log' => true, 'ai-stager.latency_ms' => 0]);

    Log::shouldReceive('info')
        ->once()
        ->with('[AI Stager] Intercepted prompt', \Mockery::type('array'));

    driver()->prompt(agentPrompt());
});

it('does not log when log is disabled', function () {
    config(['ai-stager.log' => false, 'ai-stager.latency_ms' => 0]);

    Log::shouldReceive('info')->never();

    driver()->prompt(agentPrompt());
});
