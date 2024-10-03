<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use SplFileInfo;
use Tempest\Container\Container;
use Tempest\Core\DiscoversPath;
use Tempest\Core\Discovery;
use Tempest\Core\DoNotDiscover;
use Tempest\Core\Kernel;
use Tempest\Reflection\ClassReflector;
use Throwable;

/** @internal */
final readonly class LoadDiscoveryClasses
{
    public function __construct(
        private Kernel $kernel,
        private Container $container,
    ) {
    }

    public function __invoke(): void
    {
        reset($this->kernel->discoveryClasses);

        while ($discoveryClass = current($this->kernel->discoveryClasses)) {
            /** @var Discovery $discovery */
            $discovery = $this->container->get($discoveryClass);

            try {
                if ($this->kernel->discoveryCache && $discovery->hasCache()) {
                    $discovery->restoreCache($this->container);
                    next($this->kernel->discoveryClasses);

                    continue;
                }
            } catch (ReflectionException) {
                // Invalid cache
            }

            foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
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
                            // Nothing should happen
                        }
                    }

                    if ($input instanceof ClassReflector) {
                        if ($this->shouldDiscover($discovery, $input)) {
                            $discovery->discover($input);
                        }
                    } elseif ($discovery instanceof DiscoversPath) {
                        $discovery->discoverPath($input);
                    }
                }
            }

            next($this->kernel->discoveryClasses);

            if ($this->kernel->discoveryCache) {
                $discovery->storeCache();
            }
        }
    }

    private function shouldDiscover(Discovery $discovery, ClassReflector $input): bool
    {
        if (is_null($attribute = $input->getAttribute(DoNotDiscover::class))) {
            return true;
        }

        return in_array($discovery::class, $attribute->except, strict: true);
    }
}
