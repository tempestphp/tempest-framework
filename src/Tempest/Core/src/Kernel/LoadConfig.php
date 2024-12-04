<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Core\ConfigCache;
use Tempest\Core\Kernel;

/** @internal */
final readonly class LoadConfig
{
    public function __construct(
        private Kernel $kernel,
        private ConfigCache $cache,
    ) {
    }

    public function __invoke(): void
    {
        $configPaths = $this->cache->resolve(
            'config_cache',
            fn () => $this->find(),
        );

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

        return $configPaths;
    }
}
