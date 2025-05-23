<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Core\ConfigCache;
use Tempest\Core\Kernel;
use Tempest\Support\Arr;

/** @internal */
final readonly class LoadConfig
{
    public function __construct(
        private Kernel $kernel,
        private ConfigCache $cache,
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
        $configPaths = [];

        // Scan for config files in all discovery locations
        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            $directories = new RecursiveDirectoryIterator($discoveryLocation->path, FilesystemIterator::UNIX_PATHS | FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($directories);

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if (! str_ends_with($file->getPathname(), '.config.php')) {
                    continue;
                }

                $configPaths[] = $file->getPathname();
            }
        }

        return Arr\sort_by_callback($configPaths, function (string $path1, string $path2) {
            $getPriority = fn (string $path): int => match (true) {
                str_contains($path, '/vendor/') => 0,
                str_contains($path, '.local.config.php') => 5,
                str_contains($path, '.dev.config.php') => 5,
                str_contains($path, '.production.config.php') => 4,
                str_contains($path, '.prod.config.php') => 4,
                str_contains($path, '.prd.config.php') => 4,
                str_contains($path, '.staging.config.php') => 3,
                str_contains($path, '.stg.config.php') => 3,
                str_contains($path, '.test.config.php') => 3,
                str_contains($path, '.config.php') => 2,
                default => 1,
            };

            $priorityA = $getPriority($path1);
            $priorityB = $getPriority($path2);

            if ($priorityA !== $priorityB) {
                return $priorityA <=> $priorityB;
            }

            return strcmp($path1, $path2);
        });
    }
}
