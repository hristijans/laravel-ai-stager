<?php

namespace Hristijans\AiStager\Tests\Support;

use BadMethodCallException;
use Illuminate\Broadcasting\Channel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\QueuedAgentResponse;
use Laravel\Ai\Responses\StreamableAgentResponse;

/**
 * Minimal Agent stub for use in FixtureResolver and StagerDriver tests.
 * Only the class identity matters — no methods are actually called by the stager.
 */
class FakeAgent implements Agent
{
    public function instructions(): string
    {
        return 'Fake agent instructions.';
    }

    public function prompt(string $prompt, array $attachments = [], ?string $provider = null, ?string $model = null): AgentResponse
    {
        throw new BadMethodCallException('FakeAgent::prompt() is a stub.');
    }

    public function stream(string $prompt, array $attachments = [], array|string|null $provider = null, ?string $model = null): StreamableAgentResponse
    {
        throw new BadMethodCallException('FakeAgent::stream() is a stub.');
    }

    public function queue(string $prompt, array $attachments = [], array|string|null $provider = null, ?string $model = null): QueuedAgentResponse
    {
        throw new BadMethodCallException('FakeAgent::queue() is a stub.');
    }

    public function broadcast(string $prompt, Channel|array $channels, array $attachments = [], bool $now = false, ?string $provider = null, ?string $model = null): StreamableAgentResponse
    {
        throw new BadMethodCallException('FakeAgent::broadcast() is a stub.');
    }

    public function broadcastNow(string $prompt, Channel|array $channels, array $attachments = [], ?string $provider = null, ?string $model = null): StreamableAgentResponse
    {
        throw new BadMethodCallException('FakeAgent::broadcastNow() is a stub.');
    }

    public function broadcastOnQueue(string $prompt, Channel|array $channels, array $attachments = [], ?string $provider = null, ?string $model = null): QueuedAgentResponse
    {
        throw new BadMethodCallException('FakeAgent::broadcastOnQueue() is a stub.');
    }
}
