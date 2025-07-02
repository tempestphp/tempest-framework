<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use Tempest\Core\AppConfig;
use Tempest\Core\ConfigCache;
use Tempest\Core\Kernel;
use Tempest\Support\Arr\MutableArray;
use Tempest\Support\Str;

/** @internal */
final readonly class LoadConfig
{
    public function __construct(
        private Kernel $kernel,
        private ConfigCache $cache,
        private AppConfig $appConfig,
    ) {}

    public function __invoke(): void
    {
        $configPaths = $this->cache->resolve('config_cache', fn () => $this->find());

        foreach ($configPaths as $path) {
            $configFile = require $path;

            $this->kernel->container->config($configFile);
        }
    }

    /**
     * @return string[]
     */
    public function find(): array
    {
        $configPaths = new MutableArray();

        // Scan for config files in all discovery locations
        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            $this->scan($discoveryLocation->path, $configPaths);
        }

        $suffixes = [
            'production' => ['.production.config.php', '.prod.config.php', '.prd.config.php'],
            'staging' => ['.staging.config.php', '.stg.config.php'],
            'testing' => ['.test.config.php'],
            'development' => ['.dev.config.php', '.local.config.php'],
        ];

        return $configPaths
            ->filter(fn (string $path) => match (true) {
                $this->appConfig->environment->isLocal() => ! Str\contains($path, [...$suffixes['production'], ...$suffixes['staging'], ...$suffixes['testing']]),
                $this->appConfig->environment->isProduction() => ! Str\contains($path, [...$suffixes['staging'], ...$suffixes['testing'], ...$suffixes['development']]),
                $this->appConfig->environment->isStaging() => ! Str\contains($path, [...$suffixes['testing'], ...$suffixes['development'], ...$suffixes['production']]),
                default => true,
            })
            ->sortByCallback(function (string $path1, string $path2) use ($suffixes): int {
                $getPriority = fn (string $path): int => match (true) {
                    Str\contains($path, '/vendor/') => 0,
                    Str\contains($path, $suffixes['testing']) => 6,
                    Str\contains($path, $suffixes['development']) => 5,
                    Str\contains($path, $suffixes['production']) => 4,
                    Str\contains($path, $suffixes['staging']) => 3,
                    Str\contains($path, '.config.php') => 2,
                    default => 1,
                };

                $priorityA = $getPriority($path1);
                $priorityB = $getPriority($path2);

                if ($priorityA !== $priorityB) {
                    return $priorityA <=> $priorityB;
                }

                return strcmp($path1, $path2);
            })
            ->values()
            ->toArray();
    }

    /**
     * Recursively scan a directory and apply a given set of discovery classes to all files
     */
    private function scan(string $path, MutableArray $configPaths): void
    {
        $input = realpath($path);

        // Make sure the path is valid
        if ($input === false) {
            return;
        }

        // Directories are scanned recursively
        if (is_dir($input)) {
            // Make sure the current directory is not marked for skipping
            if ($this->shouldSkipDirectory($input)) {
                return;
            }

            foreach (scandir($input, SCANDIR_SORT_NONE) as $subPath) {
                // `.` and `..` are skipped
                if ($subPath === '.' || $subPath === '..') {
                    continue;
                }

                // Scan all files and folders within this directory
                $this->scan("{$input}/{$subPath}", $configPaths);
            }

            return;
        }

        // Only include config files
        if (! str_ends_with($input, '.config.php')) {
            return;
        }

        $configPaths[] = $input;
    }

    /**
     * Check whether a given directory should be skipped
     */
    private function shouldSkipDirectory(string $path): bool
    {
        $directory = pathinfo($path, PATHINFO_BASENAME);

        return $directory === 'node_modules' || $directory === 'vendor';
    }
}
