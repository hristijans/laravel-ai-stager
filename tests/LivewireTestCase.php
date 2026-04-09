<?php

namespace Hristijans\AiStager\Tests;

use Livewire\LivewireServiceProvider;

/**
 * TestCase that boots the application with AI_STAGER_ENABLED=true
 * and the Livewire service provider registered.
 */
class LivewireTestCase extends StagerEnabledTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ...parent::getPackageProviders($app),
            LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // Livewire requires an app key for its encrypted snapshot payloads.
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
