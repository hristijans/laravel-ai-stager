<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Livewire;

use Hristijans\AiStager\Support\InterceptLogger;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class InterceptLog extends Component
{
    use WithPagination;

    public bool $cleared = false;

    public function clearLog(): void
    {
        app(InterceptLogger::class)->clear();
        $this->cleared = true;
    }

    public function render(): View
    {
        $entries = app(InterceptLogger::class)->all();
        $perPage = 50;
        $page = max(1, (int) ($this->paginators['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $pageEntries = array_slice($entries, $offset, $perPage);
        $total = count($entries);

        return view('ai-stager::livewire.intercept-log', [
            'entries'     => $pageEntries,
            'total'       => $total,
            'currentPage' => $page,
            'lastPage'    => (int) ceil($total / $perPage) ?: 1,
            'perPage'     => $perPage,
        ]);
    }
}
