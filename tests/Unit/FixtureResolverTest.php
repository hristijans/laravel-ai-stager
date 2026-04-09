<?php

use Hristijans\AiStager\Fixtures\FixtureResolver;
use Hristijans\AiStager\StagerDriver;
use Hristijans\AiStager\Tests\StagerEnabledTestCase;
use Hristijans\AiStager\Tests\Support\FakeAgent;
use Laravel\Ai\Prompts\AgentPrompt;

uses(StagerEnabledTestCase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function makePrompt(string $text, ?object $agent = null): AgentPrompt
{
    return new AgentPrompt(
        agent: $agent ?? new FakeAgent,
        prompt: $text,
        attachments: [],
        provider: app(StagerDriver::class),
        model: 'stager',
    );
}

function resolver(): FixtureResolver
{
    return new FixtureResolver;
}

// ---------------------------------------------------------------------------
// Default strategy
// ---------------------------------------------------------------------------

it('returns the inline default fixture text', function () {
    config(['ai-stager.agents.*' => ['strategy' => 'default', 'default' => 'Hello from staging!']]);

    expect(resolver()->resolve(makePrompt('anything')))->toBe('Hello from staging!');
});

it('falls back to built-in sentinel when no config exists', function () {
    config(['ai-stager.agents' => []]);

    expect(resolver()->resolve(makePrompt('anything')))
        ->toBe('Simulated AI response for staging.');
});

it('reads from file when the default value is an existing path', function () {
    $dir = resource_path('ai-fixtures');
    @mkdir($dir, recursive: true);
    file_put_contents($dir.'/test-fixture.txt', 'File-based response.');

    config(['ai-stager.agents.*' => ['strategy' => 'default', 'default' => 'test-fixture.txt']]);

    expect(resolver()->resolve(makePrompt('anything')))->toBe('File-based response.');

    unlink($dir.'/test-fixture.txt');
});

// ---------------------------------------------------------------------------
// Sequence strategy
// ---------------------------------------------------------------------------

it('cycles through sequence fixtures in order', function () {
    config(['ai-stager.agents.*' => [
        'strategy'  => 'sequence',
        'fixtures'  => ['First', 'Second', 'Third'],
    ]]);

    $r = resolver();

    expect($r->resolve(makePrompt('x')))->toBe('First');
    expect($r->resolve(makePrompt('x')))->toBe('Second');
    expect($r->resolve(makePrompt('x')))->toBe('Third');
});

it('loops back to the first fixture after the last one', function () {
    config(['ai-stager.agents.*' => [
        'strategy' => 'sequence',
        'fixtures'  => ['A', 'B'],
    ]]);

    $r = resolver();
    $r->resolve(makePrompt('x')); // A
    $r->resolve(makePrompt('x')); // B

    expect($r->resolve(makePrompt('x')))->toBe('A');
});

it('tracks sequence state independently per agent class', function () {
    $agentA = new FakeAgent;
    $agentB = new class extends FakeAgent {};

    config(['ai-stager.agents' => [
        FakeAgent::class => ['strategy' => 'sequence', 'fixtures' => ['A1', 'A2']],
        get_class($agentB) => ['strategy' => 'sequence', 'fixtures' => ['B1', 'B2']],
    ]]);

    $r = resolver();

    expect($r->resolve(makePrompt('x', $agentA)))->toBe('A1');
    expect($r->resolve(makePrompt('x', $agentB)))->toBe('B1');
    expect($r->resolve(makePrompt('x', $agentA)))->toBe('A2');
    expect($r->resolve(makePrompt('x', $agentB)))->toBe('B2');
});

// ---------------------------------------------------------------------------
// Match strategy
// ---------------------------------------------------------------------------

it('returns the fixture for the first matching keyword', function () {
    config(['ai-stager.agents.*' => [
        'strategy' => 'match',
        'keywords' => [
            'refund'  => 'Refund response.',
            'billing' => 'Billing response.',
        ],
        'default' => 'Generic response.',
    ]]);

    expect(resolver()->resolve(makePrompt('I need a refund please')))->toBe('Refund response.');
});

it('falls back to default when no keyword matches', function () {
    config(['ai-stager.agents.*' => [
        'strategy' => 'match',
        'keywords' => ['refund' => 'Refund response.'],
        'default'  => 'Generic fallback.',
    ]]);

    expect(resolver()->resolve(makePrompt('Hello there')))->toBe('Generic fallback.');
});

// ---------------------------------------------------------------------------
// Agent-specific vs catch-all config
// ---------------------------------------------------------------------------

it('uses agent-specific config over the catch-all', function () {
    config(['ai-stager.agents' => [
        '*'              => ['strategy' => 'default', 'default' => 'Catch-all response.'],
        FakeAgent::class => ['strategy' => 'default', 'default' => 'Agent-specific response.'],
    ]]);

    expect(resolver()->resolve(makePrompt('x', new FakeAgent)))->toBe('Agent-specific response.');
});

it('falls back to catch-all for unmapped agents', function () {
    $unknown = new class extends FakeAgent {};

    config(['ai-stager.agents' => [
        '*' => ['strategy' => 'default', 'default' => 'Catch-all response.'],
    ]]);

    expect(resolver()->resolve(makePrompt('x', $unknown)))->toBe('Catch-all response.');
});
