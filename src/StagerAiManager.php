<?php

namespace Hristijans\AiStager;

use Laravel\Ai\AiManager;

/**
 * Decorator for AiManager that routes ALL provider resolution to the stager driver,
 * regardless of which provider was explicitly requested.
 *
 * This solves both Path A (default provider) and Path B (explicit provider) calls:
 *   - agent()->prompt('Hello')                          ← Path A, intercepted
 *   - agent()->prompt('Hello', provider: Lab::OpenAI)  ← Path B, intercepted
 *   - agent()->prompt('Hello', provider: ['openai','anthropic']) ← also intercepted
 *
 * INVARIANT: instance() must ALWAYS return the stager driver. Never pass through.
 */
class StagerAiManager extends AiManager
{
    public function __construct(
        private readonly AiManager $inner,
        $app,
    ) {
        parent::__construct($app);
    }

    /**
     * Always return the stager driver, ignoring the requested provider name entirely.
     */
    public function instance($name = null): mixed
    {
        return $this->inner->instance('stager');
    }

    /**
     * Proxy all other method calls to the inner manager so that
     * fake(), extend(), and other SDK utilities still work normally.
     */
    public function __call($name, $arguments): mixed
    {
        return $this->inner->{$name}(...$arguments);
    }
}
