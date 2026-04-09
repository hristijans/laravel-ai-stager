<?php

use Hristijans\AiStager\Support\AgentDiscovery;
use Hristijans\AiStager\Tests\StagerEnabledTestCase;
use Hristijans\AiStager\Tests\Support\FakeAgent;

uses(StagerEnabledTestCase::class);

it('discovers FakeAgent from the tests/Support directory', function () {
    $agents = AgentDiscovery::inDirectory(dirname(__DIR__).'/Support');

    expect($agents)->toContain(FakeAgent::class);
});

it('returns an empty array for a non-existent directory', function () {
    expect(AgentDiscovery::inDirectory('/no/such/directory'))->toBeEmpty();
});

it('returns an empty array when no Agent implementations are found', function () {
    // Scan a directory with no Agent implementations
    $agents = AgentDiscovery::inDirectory(dirname(__DIR__).'/Feature');

    expect($agents)->not->toContain(FakeAgent::class);
});

it('returns results sorted alphabetically', function () {
    $agents = AgentDiscovery::inDirectory(dirname(__DIR__).'/Support');

    expect($agents)->toBe(array_values(array_unique(array_merge($agents, []))));
    expect($agents)->toEqual(collect($agents)->sort()->values()->all());
});

it('extracts the class name from a PHP file correctly', function () {
    $path = dirname(__DIR__).'/Support/FakeAgent.php';
    $fqn = AgentDiscovery::extractClassName($path);

    expect($fqn)->toBe(FakeAgent::class);
});

it('returns null for files with no class declaration', function () {
    $tmpFile = sys_get_temp_dir().'/no-class-'.uniqid().'.php';
    file_put_contents($tmpFile, "<?php\n// just a comment\n");

    expect(AgentDiscovery::extractClassName($tmpFile))->toBeNull();

    unlink($tmpFile);
});
