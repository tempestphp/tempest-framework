<?php

declare(strict_types=1);

namespace Tempest\Bootstraps;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use Tempest\CoreConfig;
use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Throwable;

final readonly class DiscoveryBootstrap implements Bootstrap
{
    public function __construct(
        private CoreConfig $coreConfig,
        private Container $container,
    ) {
    }

    public function boot(): void
    {
        reset($this->coreConfig->discoveryClasses);

        while ($discoveryClass = current($this->coreConfig->discoveryClasses)) {
            /** @var Discovery $discovery */
            $discovery = $this->container->get($discoveryClass);

            if ($this->coreConfig->discoveryCache && $discovery->hasCache()) {
                $discovery->restoreCache($this->container);
                next($this->coreConfig->discoveryClasses);

                continue;
            }

            foreach ($this->coreConfig->discoveryLocations as $discoveryLocation) {
                $directories = new RecursiveDirectoryIterator($discoveryLocation->path);
                $files = new RecursiveIteratorIterator($directories);

                /** @var SplFileInfo $file */
                foreach ($files as $file) {
                    $fileName = $file->getFilename();

                    if (
                        $fileName === ''
                        || $fileName === '.'
                        || $fileName === '..'
                        || ucfirst($fileName) !== $fileName
                    ) {
                        continue;
                    }

                    $className = str_replace(
                        [$discoveryLocation->path, '/', '.php', '\\\\'],
                        [$discoveryLocation->namespace, '\\', '', '\\'],
                        $file->getPathname(),
                    );

                    try {
                        $reflection = new ReflectionClass($className);
                    } catch (Throwable) {
                        continue;
                    }

                    $discovery->discover($reflection);
                }
            }

            next($this->coreConfig->discoveryClasses);

            $discovery->storeCache();
        }
    }
}
