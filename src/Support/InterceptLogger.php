<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Stores intercept events in the cache so the dashboard can display them.
 *
 * Entries are capped at ai-stager.dashboard.log_max and expire after
 * ai-stager.dashboard.log_ttl seconds. The most recent entry is at index 0.
 */
class InterceptLogger
{
    public const CACHE_KEY = 'ai-stager:log';

    /**
     * @param  array<string, mixed>  $context
     */
    public function record(string $operation, array $context = []): void
    {
        $ttl = (int) config('ai-stager.dashboard.log_ttl', 3600);
        $max = (int) config('ai-stager.dashboard.log_max', 200);

        /** @var array<int, array<string, mixed>> $entries */
        $entries = Cache::get(self::CACHE_KEY, []);

        array_unshift($entries, [
            'operation' => $operation,
            'agent'     => $context['agent'] ?? null,
            'context'   => array_diff_key($context, ['agent' => '']),
            'timestamp' => now()->toDateTimeString(),
        ]);

        if (count($entries) > $max) {
            $entries = array_slice($entries, 0, $max);
        }

        Cache::put(self::CACHE_KEY, $entries, $ttl);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return Cache::get(self::CACHE_KEY, []);
    }

    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
