<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Laravel\Ai\Contracts\Agent;

class MakeFixtureCommand extends Command
{
    protected $signature = 'ai-stager:make-fixture
        {agent : Fully-qualified agent class name}
        {--path= : Fixture file path relative to resources/ai-fixtures/ (defaults to agents/{kebab-name}.txt)}
        {--strategy=default : Fixture strategy for the generated config snippet (default, sequence, match)}';

    protected $description = 'Scaffold a fixture file for a Laravel AI agent';

    public function handle(): int
    {
        $agentClass = (string) $this->argument('agent');

        if (! class_exists($agentClass)) {
            $this->error("Agent class [{$agentClass}] does not exist.");

            return self::FAILURE;
        }

        if (! is_a($agentClass, Agent::class, allow_string: true)) {
            $this->error("Class [{$agentClass}] does not implement ".Agent::class.'.');

            return self::FAILURE;
        }

        $fixturePath = $this->option('path') ?: $this->deriveFixturePath($agentClass);
        $absolutePath = resource_path('ai-fixtures/'.ltrim($fixturePath, '/'));

        if (file_exists($absolutePath) && ! $this->confirmOverwrite($absolutePath)) {
            return self::SUCCESS;
        }

        $this->writeFixture($absolutePath, $agentClass);

        $this->components->info("Fixture created: <comment>{$absolutePath}</comment>");
        $this->newLine();
        $this->showConfigSnippet($agentClass, $fixturePath);

        return self::SUCCESS;
    }

    private function deriveFixturePath(string $agentClass): string
    {
        return 'agents/'.Str::kebab(class_basename($agentClass)).'.txt';
    }

    private function confirmOverwrite(string $absolutePath): bool
    {
        $this->components->warn("Fixture already exists at: <comment>{$absolutePath}</comment>");

        return $this->confirm('Overwrite?', default: false);
    }

    private function writeFixture(string $absolutePath, string $agentClass): void
    {
        $dir = dirname($absolutePath);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, recursive: true);
        }

        file_put_contents($absolutePath, $this->placeholderContent($agentClass));
    }

    private function placeholderContent(string $agentClass): string
    {
        return <<<TEXT
        Simulated AI response for staging.

        Replace this with the fixture text you want returned for {$agentClass}.
        TEXT;
    }

    private function showConfigSnippet(string $agentClass, string $fixturePath): void
    {
        $strategy = (string) $this->option('strategy');

        $this->line('Add this entry to the <comment>agents</comment> array in <comment>config/ai-stager.php</comment>:');
        $this->newLine();
        $this->line("    '{$agentClass}' => [");
        $this->line("        'strategy' => '{$strategy}',");
        $this->line("        'default'  => '{$fixturePath}',");
        $this->line('    ],');
    }
}
