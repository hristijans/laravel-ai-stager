<?php

namespace Hristijans\AiStager;

use Hristijans\AiStager\Commands\AuditCommand;
use Hristijans\AiStager\Commands\MakeFixtureCommand;
use Hristijans\AiStager\Concerns\NullsProviderKeys;
use Hristijans\AiStager\Livewire\AgentList;
use Hristijans\AiStager\Livewire\FixtureEditor;
use Hristijans\AiStager\Livewire\InterceptLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\AiManager;
use Livewire\Livewire;

class AiStagerServiceProvider extends ServiceProvider
{
    use NullsProviderKeys;

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ai-stager.php', 'ai-stager');

        if (! config('ai-stager.enabled')) {
            // Zero overhead in production / non-staging environments.
            return;
        }

        // Register the StagerDriver as a singleton so every interception
        // returns the same instance (important for state tracking).
        $this->app->singleton(StagerDriver::class);

        // Decorate the AiManager binding so that ALL provider resolution —
        // whether via the default provider (Path A) or an explicit provider
        // argument (Path B) — routes through StagerAiManager::instance() which
        // always returns the stager driver.
        $this->app->extend(AiManager::class, function (AiManager $manager, $app) {
            // Register 'stager' as a custom driver on the inner manager BEFORE
            // wrapping it, so StagerAiManager::instance('stager') can resolve it.
            $manager->extend('stager', fn ($innerApp) => $innerApp->make(StagerDriver::class));

            return new StagerAiManager($manager, $app);
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'ai-stager');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ai-stager.php' => config_path('ai-stager.php'),
            ], 'ai-stager-config');

            $this->publishes([
                __DIR__.'/../resources/fixtures' => resource_path('ai-fixtures'),
            ], 'ai-stager-fixtures');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/ai-stager'),
            ], 'ai-stager-views');

            $this->commands([
                MakeFixtureCommand::class,
                AuditCommand::class,
            ]);
        }

        if (! config('ai-stager.enabled')) {
            return;
        }

        // Load dashboard routes and register Livewire components when enabled.
        if (config('ai-stager.dashboard.enabled', true)) {
            $this->loadRoutesFrom(__DIR__.'/../routes/stager.php');

            Livewire::component('ai-stager-agent-list', AgentList::class);
            Livewire::component('ai-stager-fixture-editor', FixtureEditor::class);
            Livewire::component('ai-stager-intercept-log', InterceptLog::class);
        }

        // Null all real provider API keys — safety net in case any AI call
        // somehow escapes the StagerAiManager decorator.
        $this->nullProviderKeys();

        if ($this->app->configurationIsCached()) {
            Log::warning('[AI Stager] Config is cached. Queue workers may have stale provider keys. Run php artisan config:clear on workers.');
        }
    }
}
