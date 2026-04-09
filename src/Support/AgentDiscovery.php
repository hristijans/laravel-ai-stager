<?php

declare(strict_types=1);

namespace Hristijans\AiStager\Support;

use Laravel\Ai\Contracts\Agent;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Scans a directory for PHP classes that implement the Agent contract.
 *
 * Shared by AuditCommand and the dashboard AgentList Livewire component.
 */
class AgentDiscovery
{
    /**
     * @return string[] Sorted list of fully-qualified agent class names.
     */
    public static function inDirectory(string $dir): array
    {
        if (! is_dir($dir)) {
            return [];
        }

        $agents = [];

        /** @var SplFileInfo[] $files */
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $fqn = self::extractClassName((string) $file->getRealPath());

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
    public static function extractClassName(string $filePath): ?string
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
}
