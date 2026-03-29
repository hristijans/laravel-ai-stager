<?php

use Hristijans\AiStager\StagerAiManager;
use Hristijans\AiStager\Tests\TestCase;
use Laravel\Ai\AiManager;

uses(TestCase::class);

it('does not decorate AiManager when stager is disabled', function () {
    $manager = app(AiManager::class);

    expect($manager)->toBeInstanceOf(AiManager::class);
    expect($manager)->not->toBeInstanceOf(StagerAiManager::class);
});
