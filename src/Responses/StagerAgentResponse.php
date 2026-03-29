<?php

namespace Hristijans\AiStager\Responses;

use Illuminate\Support\Str;
use Laravel\Ai\Responses\AgentResponse;
use Laravel\Ai\Responses\Data\Meta;
use Laravel\Ai\Responses\Data\Usage;

/**
 * AgentResponse subclass for the stager driver.
 *
 * Extends AgentResponse so callers that type-check for AgentResponse
 * continue to work. Adds __get / __isset so structured-output callers
 * can access top-level JSON keys directly on the response object:
 *
 *   $response = AI::prompt(...);
 *   $response->name;   // works if fixture text is valid JSON
 */
class StagerAgentResponse extends AgentResponse
{
    /** @var array<string, mixed>|null Lazy-decoded JSON from, or null before first access. */
    private ?array $decoded = null;

    /**
     * Construct a stager agent response from plain text.
     */
    public static function make(string $text, string $invocationId = ''): self
    {
        return new self(
            invocationId: $invocationId ?: 'stager-'.Str::uuid(),
            text: $text,
            usage: new Usage,
            meta: new Meta(provider: 'stager', model: 'stager'),
        );
    }

    /**
     * Allow structured key access when the fixture text is valid JSON.
     */
    public function __get(string $key): mixed
    {
        return $this->decodedJson()[$key] ?? null;
    }

    /**
     * Allow isset() checks on structured keys.
     */
    public function __isset(string $key): bool
    {
        return isset($this->decodedJson()[$key]);
    }

    /**
     * Lazily decode the response text as JSON. Returns [] if text is not valid JSON.
     *
     * @return array<string, mixed>
     */
    private function decodedJson(): array
    {
        if ($this->decoded === null) {
            $result = json_decode($this->text, associative: true);
            $this->decoded = is_array($result) ? $result : [];
        }

        return $this->decoded;
    }
}
