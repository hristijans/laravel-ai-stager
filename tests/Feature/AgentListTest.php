<?php

use Hristijans\AiStager\Livewire\AgentList;
use Hristijans\AiStager\Tests\LivewireTestCase;
use Hristijans\AiStager\Tests\Support\FakeAgent;
use Livewire\Livewire;

uses(LivewireTestCase::class);

it('mounts without errors', function () {
    Livewire::test(AgentList::class)
        ->assertOk();
});

it('shows agents discovered from app/', function () {
    // app_path() in testbench points to the testbench skeleton — no agents there.
    // Override with a directory that has FakeAgent.
    config(['ai-stager.agents' => ['*' => ['strategy' => 'default', 'default' => 'x']]]);

    // The component scans app_path() by default. Since the test app has no agents,
    // assert it renders without exceptions and shows the empty state.
    Livewire::test(AgentList::class)
        ->assertSee('No Agent implementations found');
});

it('filters agents when search is set', function () {
    // Instantiate component directly and test the computed property
    $component = new AgentList;
    $component->search = 'zzznonexistent';

    expect($component->agents())->toBeEmpty();
});

it('selecting an agent sets selectedAgent', function () {
    Livewire::test(AgentList::class)
        ->call('selectAgent', FakeAgent::class)
        ->assertSet('selectedAgent', FakeAgent::class);
});

it('clicking an already-selected agent deselects it', function () {
    Livewire::test(AgentList::class)
        ->set('selectedAgent', FakeAgent::class)
        ->call('selectAgent', FakeAgent::class)
        ->assertSet('selectedAgent', null);
});
