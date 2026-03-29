<?php

use Hristijans\AiStager\Tests\TestCase;

uses(TestCase::class);

it('does not null api keys when stager is disabled', function () {
    expect(config('ai.providers.openai.key'))->toBe('sk-test');
});
