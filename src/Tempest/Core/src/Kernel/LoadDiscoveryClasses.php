<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use Error;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tempest\Cache\DiscoveryCacheStrategy;
use Tempest\Container\Container;
use Tempest\Core\DiscoversPath;
use Tempest\Core\Discovery;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\DiscoveryDiscovery;
use Tempest\Core\DiscoveryItems;
use Tempest\Core\DiscoveryLocation;
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
        private DiscoveryCache $discoveryCache,
    ) {}

    public function __invoke(): void
    {
        $this->applyDiscovery($this->buildDiscovery(DiscoveryDiscovery::class));

        foreach ($this->kernel->discoveryClasses as $discoveryClass) {
            $discovery = $this->buildDiscovery($discoveryClass);
            $this->applyDiscovery($discovery);
        }
    }

    /**
     * Create a discovery instance from a class name.
     * Optionally set the cached discovery items whenever caching is enabled.
     */
    private function resolveDiscovery(string $discoveryClass): Discovery
    {
        /** @var Discovery $discovery */
        $discovery = $this->container->get($discoveryClass);

        if ($this->discoveryCache->isEnabled()) {
            $discovery->setItems(
                $this->discoveryCache->restore($discoveryClass)
                ?? new DiscoveryItems(),
            );
        } else {
            $discovery->setItems(new DiscoveryItems());
        }

        return $discovery;
    }

    /**
     * Build the one specific discovery instance.
     */
    private function buildDiscovery(string $discoveryClass): Discovery
    {
        $discovery = $this->resolveDiscovery($discoveryClass);

        if (
            $this->discoveryCache->getStrategy() === DiscoveryCacheStrategy::ALL
            && $discovery->getItems()->isLoaded()
        ) {
            return $discovery;
        }

        foreach ($this->kernel->discoveryLocations as $location) {
            if ($this->shouldSkipLocation($location, $discovery)) {
                continue;
            }

            $directories = new RecursiveDirectoryIterator($location->path, FilesystemIterator::UNIX_PATHS | FilesystemIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($directories);

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                $fileName = $file->getFilename();

                if ($fileName === '' || $fileName === '.' || $fileName === '..') {
                    continue;
                }

                $input = $file->getPathname();

                if (ucfirst($fileName) === $fileName) {
                    // TODO refactor to namespace helper
                    // Trim ending slashing from path
                    $pathWithoutSlashes = rtrim($location->path, '\\/');

                    // Try to create a PSR-compliant class name from the path
                    $className = str_replace(
                        [
                            $pathWithoutSlashes,
                            '/',
                            '\\\\',
                            '.php',
                        ],
                        [
                            $location->namespace,
                            '\\',
                            '\\',
                            '',
                        ],
                        $file->getPathname(),
                    );

                    // Discovery errors (syntax errors, missing imports, etc.)
                    // are ignored when they happen in vendor files,
                    // but they are allowed to be thrown in project code
                    if ($location->isVendor()) {
                        try {
                            $input = new ClassReflector($className);
                        } catch (Throwable|Error) {
                        }
                    } elseif (class_exists($className)) {
                        $input = new ClassReflector($className);
                    }
                }

                if ($input instanceof ClassReflector) {
                    if (! $this->shouldSkipDiscoveryForClass($discovery, $input)) {
                        $discovery->discover($location, $input);
                    }
                } elseif ($discovery instanceof DiscoversPath) {
                    $discovery->discoverPath($location, $input);
                }
            }
        }

        return $discovery;
    }

    /**
     * Apply the discovered classes and files. Also store the discovered items into cache, if caching is enabled
     */
    private function applyDiscovery(Discovery $discovery): void
    {
        $discovery->apply();

        if ($this->discoveryCache->isEnabled()) {
            $discoveryItems = $discovery->getItems();

            if ($this->discoveryCache->getStrategy() === DiscoveryCacheStrategy::PARTIAL) {
                $discoveryItems = $discoveryItems->onlyVendor();
            }

            $this->discoveryCache->store($discovery, $discoveryItems);
        }
    }

    /**
     * Check whether discovery for a specific class should be skipped based on the #[DoNotDiscover] attribute
     */
    private function shouldSkipDiscoveryForClass(Discovery $discovery, ClassReflector $input): bool
    {
        $attribute = $input->getAttribute(DoNotDiscover::class);

        if ($attribute === null) {
            return false;
        }

        return ! in_array($discovery::class, $attribute->except, strict: true);
    }

    /**
     * Check whether a discovery location should be skipped based on what's cached for a specific discovery class
     */
    private function shouldSkipLocation(DiscoveryLocation $location, Discovery $discovery): bool
    {
        return match ($this->discoveryCache->getStrategy()) {
            // If discovery cache is disabled, no locations should be skipped, all should always be discovered
            DiscoveryCacheStrategy::NONE => false,

            // If discover cache is enabled, all locations with valid cache should be skipped
            DiscoveryCacheStrategy::ALL => $this->discoveryCache->hasCache($discovery, $location),

            // If partial discovery cache is enabled, vendor locations with valid cache should be skipped
            DiscoveryCacheStrategy::PARTIAL => $location->isVendor() && $this->discoveryCache->hasCache($discovery, $location),
        };
    }
}
