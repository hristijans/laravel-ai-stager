<?php

use Hristijans\AiStager\Livewire\FixtureEditor;
use Hristijans\AiStager\Tests\LivewireTestCase;
use Hristijans\AiStager\Tests\Support\FakeAgent;
use Livewire\Livewire;

uses(LivewireTestCase::class);

function editorFixturePath(): string
{
    return resource_path('ai-fixtures/agents/fake-agent.txt');
}

function cleanEditorFixture(): void
{
    $path = editorFixturePath();
    if (file_exists($path)) {
        unlink($path);
    }
}

// ---------------------------------------------------------------------------
// Mount with no explicit config (catch-all / new fixture)
// ---------------------------------------------------------------------------

it('mounts with an empty textarea when agent has no explicit config', function () {
    config(['ai-stager.agents' => ['*' => ['strategy' => 'default', 'default' => 'x']]]);

    Livewire::test(FixtureEditor::class, ['agentClass' => FakeAgent::class])
        ->assertSet('content', '')
        ->assertSet('isFileBased', false);
});

it('derives a fixture path from the agent class name', function () {
    config(['ai-stager.agents' => ['*' => ['strategy' => 'default', 'default' => 'x']]]);

    Livewire::test(FixtureEditor::class, ['agentClass' => FakeAgent::class])
        ->assertSet('fixturePath', 'agents/fake-agent.txt');
});

// ---------------------------------------------------------------------------
// Mount with file-based fixture
// ---------------------------------------------------------------------------

it('loads file content when fixture is file-based', function () {
    $path = editorFixturePath();
    @mkdir(dirname($path), recursive: true);
    file_put_contents($path, 'File-based fixture content.');

    config(['ai-stager.agents' => [
        FakeAgent::class => ['strategy' => 'default', 'default' => 'agents/fake-agent.txt'],
    ]]);

    Livewire::test(FixtureEditor::class, ['agentClass' => FakeAgent::class])
        ->assertSet('content', 'File-based fixture content.')
        ->assertSet('isFileBased', true);

    cleanEditorFixture();
});

// ---------------------------------------------------------------------------
// Mount with inline fixture
// ---------------------------------------------------------------------------

it('loads inline text as content when fixture is not a file', function () {
    config(['ai-stager.agents' => [
        FakeAgent::class => ['strategy' => 'default', 'default' => 'Inline fixture text.'],
    ]]);

    Livewire::test(FixtureEditor::class, ['agentClass' => FakeAgent::class])
        ->assertSet('content', 'Inline fixture text.')
        ->assertSet('isFileBased', false);
});

// ---------------------------------------------------------------------------
// Save action
// ---------------------------------------------------------------------------

it('save() writes the content to a file', function () {
    cleanEditorFixture();
    config(['ai-stager.agents' => ['*' => ['strategy' => 'default', 'default' => 'x']]]);

    Livewire::test(FixtureEditor::class, ['agentClass' => FakeAgent::class])
        ->set('content', 'Saved from dashboard.')
        ->call('save');

    expect(file_get_contents(editorFixturePath()))->toBe('Saved from dashboard.');

    cleanEditorFixture();
});

it('save() sets saved flag to true', function () {
    cleanEditorFixture();
    config(['ai-stager.agents' => ['*' => ['strategy' => 'default', 'default' => 'x']]]);

    Livewire::test(FixtureEditor::class, ['agentClass' => FakeAgent::class])
        ->set('content', 'Hello')
        ->call('save')
        ->assertSet('saved', true);

    cleanEditorFixture();
});

it('save() shows config snippet for agents not yet in config', function () {
    cleanEditorFixture();
    config(['ai-stager.agents' => ['*' => ['strategy' => 'default', 'default' => 'x']]]);

    Livewire::test(FixtureEditor::class, ['agentClass' => FakeAgent::class])
        ->set('content', 'Hello')
        ->call('save')
        ->assertSet('configSnippet', fn ($v) => str_contains($v, FakeAgent::class));

    cleanEditorFixture();
});

it('save() does not show config snippet for agents already in config', function () {
    $path = editorFixturePath();
    @mkdir(dirname($path), recursive: true);
    file_put_contents($path, 'Old content.');

    config(['ai-stager.agents' => [
        FakeAgent::class => ['strategy' => 'default', 'default' => 'agents/fake-agent.txt'],
    ]]);

    Livewire::test(FixtureEditor::class, ['agentClass' => FakeAgent::class])
        ->set('content', 'Updated content.')
        ->call('save')
        ->assertSet('configSnippet', null);

    cleanEditorFixture();
});
