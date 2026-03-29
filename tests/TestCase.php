<?php

namespace Hristijans\AiStager\Tests;

use Hristijans\AiStager\AiStagerServiceProvider;
use Laravel\Ai\AiServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            AiServiceProvider::class,
            AiStagerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('cache.default', 'array');
        config()->set('ai.default', 'openai');
        config()->set('ai.providers', [
            'openai' => ['driver' => 'openai', 'key' => 'sk-test'],
        ]);
    }
}
