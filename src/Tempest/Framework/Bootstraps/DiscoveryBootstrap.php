<?php

declare(strict_types=1);

namespace Tempest\Framework\Bootstraps;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Container\Container;
use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Framework\Application\AppConfig;
use Tempest\Support\Reflection\ClassReflector;
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
                $directories = new RecursiveDirectoryIterator($discoveryLocation->path, FilesystemIterator::UNIX_PATHS | FilesystemIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($directories);

                /** @var SplFileInfo $file */
                foreach ($files as $file) {
                    $fileName = $file->getFilename();
                    if ($fileName === '') {
                        continue;
                    }

                    if ($fileName === '.') {
                        continue;
                    }

                    if ($fileName === '..') {
                        continue;
                    }

                    $input = $file->getPathname();

                    if (ucfirst($fileName) === $fileName) {
                        // Trim ending slashing from path
                        $pathWithoutSlashes = rtrim($discoveryLocation->path, '\\/');

                        // Try to create a PSR-compliant class name from the path
                        $className = str_replace(
                            [
                                $pathWithoutSlashes,
                                '/',
                                '\\\\',
                                '.php',
                            ],
                            [
                                $discoveryLocation->namespace,
                                '\\',
                                '\\',
                                '',
                            ],
                            $file->getPathname(),
                        );

                        try {
                            $input = new ClassReflector($className);
                        } catch (Throwable) {
                            continue;
                        }
                    }

                    if ($input instanceof ClassReflector) {
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
