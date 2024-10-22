<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Core\Kernel;

/** @internal */
final readonly class LoadConfig
{
    public function __construct(
        private Kernel $kernel,
    ) {
    }

    public function __invoke(): void
    {
        // Scan for config files in all discovery locations
        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            $directories = new RecursiveDirectoryIterator($discoveryLocation->path, FilesystemIterator::UNIX_PATHS | FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($directories);

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                if (! str_ends_with($file->getPathname(), '.config.php')) {
                    continue;
                }

                $configFile = require $file->getPathname();

                $this->kernel->container->config($configFile);
            }
        }
    }
}
