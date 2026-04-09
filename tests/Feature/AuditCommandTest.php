<?php

use Hristijans\AiStager\Tests\StagerEnabledTestCase;
use Hristijans\AiStager\Tests\Support\FakeAgent;

uses(StagerEnabledTestCase::class);

// ---------------------------------------------------------------------------
// No agents found
// ---------------------------------------------------------------------------

it('warns when no agents are found in the scan directory', function () {
    // Scan a directory that has no Agent implementations
    $this->artisan('ai-stager:audit', ['--dir' => 'config'])
        ->expectsOutputToContain('No classes implementing')
        ->assertSuccessful();
});

it('handles a non-existent scan directory gracefully', function () {
    $this->artisan('ai-stager:audit', ['--dir' => 'non-existent-directory'])
        ->expectsOutputToContain('No classes implementing')
        ->assertSuccessful();
});

// ---------------------------------------------------------------------------
// Agent discovery and config reporting
// ---------------------------------------------------------------------------

it('marks agents with explicit config as explicit', function () {
    config(['ai-stager.agents' => [
        FakeAgent::class => ['strategy' => 'default', 'default' => 'Hello staging.'],
        '*'              => ['strategy' => 'default', 'default' => 'Catch-all.'],
    ]]);

    // Scan the tests/Support directory which contains FakeAgent
    $this->artisan('ai-stager:audit', ['--dir' => dirname(__DIR__).'/Support'])
        ->expectsOutputToContain('explicit')
        ->assertSuccessful();
});

it('marks agents without explicit config as catch-all', function () {
    // Only the '*' catch-all is configured — FakeAgent has no explicit entry
    config(['ai-stager.agents' => [
        '*' => ['strategy' => 'default', 'default' => 'Catch-all.'],
    ]]);

    $this->artisan('ai-stager:audit', ['--dir' => dirname(__DIR__).'/Support'])
        ->expectsOutputToContain('catch-all')
        ->assertSuccessful();
});

it('shows the strategy for explicitly configured agents', function () {
    config(['ai-stager.agents' => [
        FakeAgent::class => ['strategy' => 'sequence', 'fixtures' => ['a', 'b']],
        '*'              => ['strategy' => 'default', 'default' => 'Catch-all.'],
    ]]);

    $this->artisan('ai-stager:audit', ['--dir' => dirname(__DIR__).'/Support'])
        ->expectsOutputToContain('sequence')
        ->assertSuccessful();
});

it('shows a summary line with counts', function () {
    config(['ai-stager.agents' => [
        FakeAgent::class => ['strategy' => 'default', 'default' => 'Staged.'],
        '*'              => ['strategy' => 'default', 'default' => 'Catch-all.'],
    ]]);

    // Use Artisan::call() so Artisan::output() captures the result
    \Illuminate\Support\Facades\Artisan::call('ai-stager:audit', [
        '--dir' => dirname(__DIR__).'/Support',
    ]);

    expect(\Illuminate\Support\Facades\Artisan::output())
        ->toContain('agents have explicit fixture config');
});
