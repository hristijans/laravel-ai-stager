<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Livewire;

use Hristijans\AiStager\Support\AgentDiscovery;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class AgentList extends Component
{
    public string $search = '';

    public ?string $selectedAgent = null;

    /** @var string[] */
    protected array $allAgents = [];

    public function mount(): void
    {
        $this->allAgents = AgentDiscovery::inDirectory(app_path());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    #[Computed]
    public function agents(): array
    {
        /** @var array<string, array<string, mixed>> $configuredAgents */
        $configuredAgents = config('ai-stager.agents', []);
        $search = strtolower($this->search);

        return collect($this->allAgents)
            ->when($search !== '', fn ($c) => $c->filter(
                fn (string $class) => str_contains(strtolower($class), $search),
            ))
            ->map(function (string $agentClass) use ($configuredAgents) {
                $isExplicit = isset($configuredAgents[$agentClass]);
                $config = $isExplicit ? $configuredAgents[$agentClass] : ($configuredAgents['*'] ?? []);

                return [
                    'class'      => $agentClass,
                    'shortName'  => class_basename($agentClass),
                    'strategy'   => $config['strategy'] ?? '—',
                    'isExplicit' => $isExplicit,
                ];
            })
            ->values()
            ->all();
    }

    public function selectAgent(string $agentClass): void
    {
        $this->selectedAgent = $agentClass === $this->selectedAgent ? null : $agentClass;
    }

    public function render(): View
    {
        return view('ai-stager::livewire.agent-list');
    }
}
