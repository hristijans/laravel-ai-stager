<?php

use Hristijans\AiStager\Support\InterceptLogger;
use Hristijans\AiStager\Tests\StagerEnabledTestCase;
use Illuminate\Support\Facades\Cache;

uses(StagerEnabledTestCase::class);

function interceptLogger(): InterceptLogger
{
    return new InterceptLogger;
}

it('records an entry in the cache', function () {
    interceptLogger()->record('prompt', ['agent' => 'App\Agents\Test']);

    $entries = Cache::get(InterceptLogger::CACHE_KEY);
    expect($entries)->toHaveCount(1);
    expect($entries[0]['operation'])->toBe('prompt');
    expect($entries[0]['agent'])->toBe('App\Agents\Test');
});

it('prepends new entries so the most recent is first', function () {
    interceptLogger()->record('prompt');
    interceptLogger()->record('audio');

    $entries = Cache::get(InterceptLogger::CACHE_KEY);
    expect($entries[0]['operation'])->toBe('audio');
    expect($entries[1]['operation'])->toBe('prompt');
});

it('caps entries at log_max', function () {
    config(['ai-stager.dashboard.log_max' => 3]);

    for ($i = 0; $i < 5; $i++) {
        interceptLogger()->record('prompt');
    }

    expect(Cache::get(InterceptLogger::CACHE_KEY))->toHaveCount(3);
});

it('returns all entries via all()', function () {
    interceptLogger()->record('image');
    interceptLogger()->record('embeddings');

    expect(interceptLogger()->all())->toHaveCount(2);
});

it('clears all entries', function () {
    interceptLogger()->record('prompt');
    interceptLogger()->clear();

    expect(interceptLogger()->all())->toBeEmpty();
});

it('stores a timestamp on each entry', function () {
    interceptLogger()->record('rerank');

    $entry = Cache::get(InterceptLogger::CACHE_KEY)[0];
    expect($entry['timestamp'])->not->toBeEmpty();
});

it('separates agent from other context keys', function () {
    interceptLogger()->record('prompt', ['agent' => 'MyAgent', 'inputs' => 3]);

    $entry = Cache::get(InterceptLogger::CACHE_KEY)[0];
    expect($entry['agent'])->toBe('MyAgent');
    expect($entry['context'])->toHaveKey('inputs');
    expect($entry['context'])->not->toHaveKey('agent');
});
