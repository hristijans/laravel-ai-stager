<?php

use Hristijans\AiStager\StagerAiManager;
use Hristijans\AiStager\StagerDriver;
use Hristijans\AiStager\Tests\StagerEnabledTestCase;
use Laravel\Ai\AiManager;
use Laravel\Ai\Enums\Lab;

uses(StagerEnabledTestCase::class);

it('decorates AiManager with StagerAiManager when stager is enabled', function () {
    expect(app(AiManager::class))->toBeInstanceOf(StagerAiManager::class);
});

it('routes default provider calls to the stager driver', function () {
    expect(app(AiManager::class)->instance())->toBeInstanceOf(StagerDriver::class);
});

it('routes explicit string provider calls to the stager driver', function () {
    $manager = app(AiManager::class);

    expect($manager->instance('openai'))->toBeInstanceOf(StagerDriver::class);
});

it('routes explicit Lab enum provider calls to the stager driver', function () {
    // Lab::OpenAI resolves to 'openai' — should still be intercepted
    expect(app(AiManager::class)->instance(Lab::OpenAI->value))
        ->toBeInstanceOf(StagerDriver::class);
});
