<?php

use Hristijans\AiStager\Livewire\InterceptLog;
use Hristijans\AiStager\Support\InterceptLogger;
use Hristijans\AiStager\Tests\LivewireTestCase;
use Livewire\Livewire;

uses(LivewireTestCase::class);

it('renders with empty state when no entries exist', function () {
    Livewire::test(InterceptLog::class)
        ->assertSee('No intercept log entries yet');
});

it('renders entries from the cache', function () {
    app(InterceptLogger::class)->record('prompt', ['agent' => 'App\MyAgent']);

    Livewire::test(InterceptLog::class)
        ->assertSee('prompt')
        ->assertSee('App\MyAgent');
});

it('clearLog() empties the cache and shows the cleared message', function () {
    app(InterceptLogger::class)->record('image');

    Livewire::test(InterceptLog::class)
        ->call('clearLog')
        ->assertSet('cleared', true);

    expect(app(InterceptLogger::class)->all())->toBeEmpty();
});
