<?php

namespace Hristijans\AiStager\Tests;

/**
 * TestCase that boots the application with AI_STAGER_ENABLED=true.
 *
 * Uses putenv() before parent::setUp() so the env var is available when
 * LoadConfiguration runs (before RegisterProviders), ensuring that
 * AiStagerServiceProvider::register() sees enabled=true and decorates AiManager.
 */
class StagerEnabledTestCase extends TestCase
{
    protected function setUp(): void
    {
        putenv('AI_STAGER_ENABLED=true');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        putenv('AI_STAGER_ENABLED=');
    }
}
