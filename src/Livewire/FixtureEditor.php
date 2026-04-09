<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Livewire;

use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class FixtureEditor extends Component
{
    public string $agentClass = '';

    public string $content = '';

    /** Fixture path relative to resources/ai-fixtures/ — null when not yet file-based. */
    public ?string $fixturePath = null;

    public bool $isFileBased = false;

    /** Config snippet shown after creating a new fixture file. */
    public ?string $configSnippet = null;

    public bool $saved = false;

    public function mount(string $agentClass): void
    {
        $this->agentClass = $agentClass;
        $this->loadFixture();
    }

    private function loadFixture(): void
    {
        /** @var array<string, array<string, mixed>> $agents */
        $agents = config('ai-stager.agents', []);
        $config = $agents[$this->agentClass] ?? null;

        if ($config === null) {
            // No explicit config — derive a default path for when the user creates a fixture
            $this->fixturePath = 'agents/'.Str::kebab(class_basename($this->agentClass)).'.txt';
            $this->isFileBased = false;
            $this->content = '';

            return;
        }

        $rawValue = (string) ($config['default'] ?? '');
        $absolutePath = resource_path('ai-fixtures/'.ltrim($rawValue, '/'));

        if (is_file($absolutePath)) {
            // File-based fixture
            $this->fixturePath = $rawValue;
            $this->isFileBased = true;
            $this->content = (string) file_get_contents($absolutePath);
        } else {
            // Inline fixture — saving will convert it to a file
            $this->fixturePath = 'agents/'.Str::kebab(class_basename($this->agentClass)).'.txt';
            $this->isFileBased = false;
            $this->content = $rawValue;
        }
    }

    public function save(): void
    {
        $this->saved = false;
        $absolutePath = resource_path('ai-fixtures/'.ltrim((string) $this->fixturePath, '/'));
        $dir = dirname($absolutePath);

        if (! is_dir($dir)) {
            mkdir($dir, 0755, recursive: true);
        }

        file_put_contents($absolutePath, $this->content);
        $this->isFileBased = true;
        $this->saved = true;

        if ($this->configSnippet === null) {
            // Show snippet only if this was not previously file-based (first save)
            /** @var array<string, array<string, mixed>> $agents */
            $agents = config('ai-stager.agents', []);
            $alreadyConfigured = isset($agents[$this->agentClass]);

            if (! $alreadyConfigured) {
                $this->configSnippet = $this->buildConfigSnippet();
            }
        }
    }

    private function buildConfigSnippet(): string
    {
        return "'{$this->agentClass}' => [\n    'strategy' => 'default',\n    'default'  => '{$this->fixturePath}',\n],";
    }

    public function render(): View
    {
        return view('ai-stager::livewire.fixture-editor');
    }
}
