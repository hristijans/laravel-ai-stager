<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Fixtures;

use Laravel\Ai\Prompts\AgentPrompt;

/**
 * Resolves fixture text for a given AgentPrompt based on the ai-stager config.
 *
 * Resolution chain:
 *   1. Look up the agent's class name in ai-stager.agents; fall back to '*'.
 *   2. Apply the configured strategy: default, sequence, or match.
 *   3. Resolve the value: if an existing file path under resources/ai-fixtures/, read it; otherwise use inline.
 *
 * Sequence state is preserved between calls because StagerDriver is a singleton
 * and FixtureResolver is injected at construction time.
 */
class FixtureResolver
{
    /** @var array<string, int> Tracks the next sequence index per agent key. */
    private array $sequenceIndices = [];

    public function resolve(AgentPrompt $prompt): string
    {
        $agentClass = get_class($prompt->agent);
        $config = $this->configFor($agentClass);

        return $this->applyStrategy($config, $prompt, $agentClass);
    }

    /**
     * @return array<string, mixed>
     */
    private function configFor(string $agentClass): array
    {
        /** @var array<string, array<string, mixed>> $agents */
        $agents = config('ai-stager.agents', []);

        return $agents[$agentClass] ?? $agents['*'] ?? [
            'strategy' => 'default',
            'default'  => 'Simulated AI response for staging.',
        ];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function applyStrategy(array $config, AgentPrompt $prompt, string $agentClass): string
    {
        return match ($config['strategy'] ?? 'default') {
            'sequence' => $this->resolveSequence($config, $agentClass),
            'match'    => $this->resolveMatch($config, $prompt),
            default    => $this->resolveValue((string) ($config['default'] ?? 'Simulated AI response for staging.')),
        };
    }

    /**
     * Cycles through the fixtures array in order, looping back to the start.
     *
     * @param  array<string, mixed>  $config
     */
    private function resolveSequence(array $config, string $agentClass): string
    {
        /** @var string[] $fixtures */
        $fixtures = $config['fixtures'] ?? [(string) ($config['default'] ?? '')];
        $count = count($fixtures);

        $index = $this->sequenceIndices[$agentClass] ?? 0;
        $value = $fixtures[$index % $count];

        $this->sequenceIndices[$agentClass] = ($index + 1) % $count;

        return $this->resolveValue($value);
    }

    /**
     * Checks keywords against the prompt text, falls back to default.
     *
     * @param  array<string, mixed>  $config
     */
    private function resolveMatch(array $config, AgentPrompt $prompt): string
    {
        /** @var array<string, string> $keywords */
        $keywords = $config['keywords'] ?? [];

        foreach ($keywords as $keyword => $fixture) {
            if ($prompt->contains((string) $keyword)) {
                return $this->resolveValue($fixture);
            }
        }

        return $this->resolveValue((string) ($config['default'] ?? 'Simulated AI response for staging.'));
    }

    /**
     * Resolves a fixture value: file path → read contents, otherwise return as-is.
     */
    private function resolveValue(string $value): string
    {
        $path = resource_path('ai-fixtures/'.ltrim($value, '/'));

        if (is_file($path)) {
            return (string) file_get_contents($path);
        }

        return $value;
    }
}
