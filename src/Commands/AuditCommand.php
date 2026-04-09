<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Commands;

use Illuminate\Console\Command;
use Laravel\Ai\Contracts\Agent;
use SplFileInfo;

class AuditCommand extends Command
{
    protected $signature = 'ai-stager:audit
        {--dir=app : Directory to scan for agent classes, relative to base_path() (absolute paths accepted)}';

    protected $description = 'Report AI agent fixture coverage for the stager';

    public function handle(): int
    {
        $dir = $this->resolveDir();
        $agents = $this->discoverAgents($dir);

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
     * Discover all classes in the scan directory that implement Agent.
     *
     * @return string[]
     */
    private function discoverAgents(string $dir): array
    {
        if (! is_dir($dir)) {
            return [];
        }

        $agents = [];

        /** @var SplFileInfo[] $files */
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $fqn = $this->extractClassName((string) $file->getRealPath());

            if ($fqn === null || ! class_exists($fqn)) {
                continue;
            }

            if (is_a($fqn, Agent::class, allow_string: true)) {
                $agents[] = $fqn;
            }
        }

        sort($agents);

        return $agents;
    }

    /**
     * Parse the fully-qualified class name from a PHP file using regex.
     */
    private function extractClassName(string $filePath): ?string
    {
        $contents = (string) file_get_contents($filePath);

        preg_match('/^namespace\s+([^;{]+)[;{]/m', $contents, $nsMatch);
        $namespace = isset($nsMatch[1]) ? trim($nsMatch[1]) : '';

        preg_match('/^(?:abstract\s+|final\s+)?class\s+(\w+)/m', $contents, $classMatch);
        $className = $classMatch[1] ?? '';

        if ($className === '') {
            return null;
        }

        return $namespace !== '' ? $namespace.'\\'.$className : $className;
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
