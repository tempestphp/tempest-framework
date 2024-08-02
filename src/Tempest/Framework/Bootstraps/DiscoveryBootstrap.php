<?php

declare(strict_types=1);

namespace Tempest\Framework\Bootstraps;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;
use Tempest\Container\Container;
use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Framework\Application\AppConfig;
use Throwable;

final readonly class DiscoveryBootstrap implements Bootstrap
{
    public function __construct(
        private AppConfig $appConfig,
        private Container $container,
    ) {
    }

    public function boot(): void
    {
        reset($this->appConfig->discoveryClasses);

        while ($discoveryClass = current($this->appConfig->discoveryClasses)) {
            /** @var Discovery $discovery */
            $discovery = $this->container->get($discoveryClass);

            if ($this->appConfig->discoveryCache && $discovery->hasCache()) {
                $discovery->restoreCache($this->container);
                next($this->appConfig->discoveryClasses);

                continue;
            }

            foreach ($this->appConfig->discoveryLocations as $discoveryLocation) {
                $directories = new RecursiveDirectoryIterator($discoveryLocation->path);
                $files = new RecursiveIteratorIterator($directories);

                /** @var SplFileInfo $file */
                foreach ($files as $file) {
                    $fileName = $file->getFilename();

                    if (
                        $fileName === ''
                        || $fileName === '.'
                        || $fileName === '..'
                    ) {
                        continue;
                    }

                    $input = $file->getPathname();

                    if (ucfirst($fileName) === $fileName) {
                        $className = str_replace(
                            [$discoveryLocation->path, '/', '.php', '\\\\'],
                            [$discoveryLocation->namespace, '\\', '', '\\'],
                            $file->getPathname(),
                        );

                        try {
                            $input = new ReflectionClass($className);
                        } catch (Throwable) {
                            continue;
                        }
                    }

                    if ($input instanceof ReflectionClass) {
                        $discovery->discover($input);
                    } elseif ($discovery instanceof DiscoversPath) {
                        $discovery->discoverPath($input);
                    }
                }
            }

            next($this->appConfig->discoveryClasses);

            $discovery->storeCache();
        }
    }
}
