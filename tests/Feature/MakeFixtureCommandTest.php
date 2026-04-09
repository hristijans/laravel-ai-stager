<?php

use Hristijans\AiStager\Tests\StagerEnabledTestCase;
use Hristijans\AiStager\Tests\Support\FakeAgent;

uses(StagerEnabledTestCase::class);

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function fixturePath(string $relative): string
{
    return resource_path('ai-fixtures/'.$relative);
}

function cleanFixture(string $relative): void
{
    $path = fixturePath($relative);
    if (file_exists($path)) {
        unlink($path);
    }
}

// ---------------------------------------------------------------------------
// Happy path
// ---------------------------------------------------------------------------

it('creates a fixture file at the derived path', function () {
    $expectedPath = fixturePath('agents/fake-agent.txt');
    cleanFixture('agents/fake-agent.txt');

    $this->artisan('ai-stager:make-fixture', ['agent' => FakeAgent::class])
        ->assertSuccessful();

    expect(file_exists($expectedPath))->toBeTrue();

    cleanFixture('agents/fake-agent.txt');
});

it('writes placeholder content to the fixture file', function () {
    cleanFixture('agents/fake-agent.txt');

    $this->artisan('ai-stager:make-fixture', ['agent' => FakeAgent::class])
        ->assertSuccessful();

    $content = file_get_contents(fixturePath('agents/fake-agent.txt'));
    expect($content)->toContain('Simulated AI response for staging.');

    cleanFixture('agents/fake-agent.txt');
});

it('respects a custom --path option', function () {
    cleanFixture('custom/my-fixture.txt');

    $this->artisan('ai-stager:make-fixture', [
        'agent'  => FakeAgent::class,
        '--path' => 'custom/my-fixture.txt',
    ])->assertSuccessful();

    expect(file_exists(fixturePath('custom/my-fixture.txt')))->toBeTrue();

    cleanFixture('custom/my-fixture.txt');
    @rmdir(resource_path('ai-fixtures/custom'));
});

it('outputs the config snippet', function () {
    cleanFixture('agents/fake-agent.txt');

    $this->artisan('ai-stager:make-fixture', ['agent' => FakeAgent::class])
        ->expectsOutputToContain(FakeAgent::class)
        ->expectsOutputToContain("'strategy'")
        ->expectsOutputToContain("'default'")
        ->assertSuccessful();

    cleanFixture('agents/fake-agent.txt');
});

it('includes the --strategy option in the config snippet', function () {
    cleanFixture('agents/fake-agent.txt');

    $this->artisan('ai-stager:make-fixture', [
        'agent'      => FakeAgent::class,
        '--strategy' => 'sequence',
    ])->expectsOutputToContain("'sequence'")
        ->assertSuccessful();

    cleanFixture('agents/fake-agent.txt');
});

// ---------------------------------------------------------------------------
// Validation
// ---------------------------------------------------------------------------

it('fails when the agent class does not exist', function () {
    $this->artisan('ai-stager:make-fixture', ['agent' => 'App\Agents\NonExistent'])
        ->assertFailed();
});

it('fails when the class does not implement Agent', function () {
    $this->artisan('ai-stager:make-fixture', ['agent' => \stdClass::class])
        ->assertFailed();
});

// ---------------------------------------------------------------------------
// Overwrite behaviour
// ---------------------------------------------------------------------------

it('warns and bails when fixture exists and user declines overwrite', function () {
    $path = fixturePath('agents/fake-agent.txt');
    @mkdir(dirname($path), recursive: true);
    file_put_contents($path, 'original');

    $this->artisan('ai-stager:make-fixture', ['agent' => FakeAgent::class])
        ->expectsConfirmation('Overwrite?', 'no')
        ->assertSuccessful();

    expect(file_get_contents($path))->toBe('original');

    cleanFixture('agents/fake-agent.txt');
});

it('overwrites the fixture when user confirms', function () {
    $path = fixturePath('agents/fake-agent.txt');
    @mkdir(dirname($path), recursive: true);
    file_put_contents($path, 'original');

    $this->artisan('ai-stager:make-fixture', ['agent' => FakeAgent::class])
        ->expectsConfirmation('Overwrite?', 'yes')
        ->assertSuccessful();

    expect(file_get_contents($path))->not->toBe('original');

    cleanFixture('agents/fake-agent.txt');
});
