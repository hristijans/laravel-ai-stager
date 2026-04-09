<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Commands;

use Hristijans\AiStager\Support\AgentDiscovery;
use Illuminate\Console\Command;
use Laravel\Ai\Contracts\Agent;

class AuditCommand extends Command
{
    protected $signature = 'ai-stager:audit
        {--dir=app : Directory to scan for agent classes, relative to base_path() (absolute paths accepted)}';

    protected $description = 'Report AI agent fixture coverage for the stager';

    public function handle(): int
    {
        $dir = $this->resolveDir();
        $agents = AgentDiscovery::inDirectory($dir);

        if (empty($agents)) {
            $this->components->warn('No classes implementing '.Agent::class." were found in [{$dir}].");

            return self::SUCCESS;
        }

        /** @var array<string, array<string, mixed>> $agentConfig */
        $agentConfig = config('ai-stager.agents', []);
        $rows = [];

        foreach ($agents as $agentClass) {
            $isExplicit = isset($agentConfig[$agentClass]);
            $config = $isExplicit ? $agentConfig[$agentClass] : ($agentConfig['*'] ?? []);

            $strategy = $config['strategy'] ?? '—';
            $fixture = $this->describeFixture($config);
            $status = $isExplicit ? '<fg=green>✓ explicit</>' : '<fg=yellow>~ catch-all</>';

            $rows[] = [$agentClass, $strategy, $fixture, $status];
        }

        $this->table(['Agent', 'Strategy', 'Fixture', 'Status'], $rows);

        $explicit = count(array_filter($rows, fn ($r) => str_contains($r[3], 'explicit')));
        $total = count($rows);
        $catchAll = $total - $explicit;

        $this->newLine();
        $this->info("{$explicit} of {$total} agents have explicit fixture config.");

        if ($catchAll > 0) {
            $this->warn("{$catchAll} ".($catchAll === 1 ? 'agent falls' : 'agents fall').' through to the catch-all.');
        }

        return self::SUCCESS;
    }

    private function resolveDir(): string
    {
        $option = (string) $this->option('dir');

        return str_starts_with($option, DIRECTORY_SEPARATOR) ? $option : base_path($option);
    }

    /**
     * Produce a human-readable description of the fixture value.
     *
     * @param  array<string, mixed>  $config
     */
    private function describeFixture(array $config): string
    {
        if (empty($config)) {
            return '—';
        }

        $strategy = $config['strategy'] ?? 'default';

        if ($strategy === 'sequence') {
            $fixtures = (array) ($config['fixtures'] ?? []);
            $count = count($fixtures);

            return "{$count} fixture".($count !== 1 ? 's' : '');
        }

        if ($strategy === 'match') {
            $keywords = array_keys((array) ($config['keywords'] ?? []));

            return 'keywords: '.implode(', ', $keywords);
        }

        $default = (string) ($config['default'] ?? '');

        if ($default === '') {
            return '—';
        }

        return strlen($default) > 40
            ? '"'.substr($default, 0, 37).'..."'
            : "\"{$default}\"";
    }
}
