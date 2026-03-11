<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use AssertionError;
use Psr\Container\ContainerInterface;
use Tempest\Container\Container;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Filesystem;
use Throwable;

/** @internal */
final class LoadDiscoveryClasses
{
    private array $appliedDiscovery = [];
    private array $shouldSkipForClass = [];

    public function __construct(
        private readonly Registry $registry,
        private readonly DiscoveryConfig $discoveryConfig,
        private readonly DiscoveryCache $discoveryCache,
        private readonly Container|ContainerInterface $container,
    ) {}

    /**
     * @param class-string<Discovery>[]|null $discoveryClasses
     * @param DiscoveryLocation[]|null $discoveryLocations
     */
    public function __invoke(
        ?array $discoveryClasses = null,
        ?array $discoveryLocations = null,
    ): void
    {
        $discoveries = $this->build($discoveryClasses, $discoveryLocations);

        foreach ($discoveries as $discovery) {
            $this->applyDiscovery($discovery);
        }
    }

    /**
     * @param class-string<Discovery>[]|null $discoveryClasses
     * @param DiscoveryLocation[]|null $discoveryLocations
     * @return Discovery[]
     */
    public function build(
        ?array $discoveryClasses = null,
        ?array $discoveryLocations = null,
    ): array
    {
        $discoveryLocations ??= $this->registry->locations;

        if ($discoveryClasses === null) {
            // DiscoveryDiscovery needs to be applied before we can build all other discoveries
            $discoveryDiscovery = $this->resolveDiscovery(DiscoveryDiscovery::class);

            // The first pass over all directories to find all discovery classes
            $this->discover([$discoveryDiscovery], $discoveryLocations);

            // Manually apply DiscoveryDiscovery
            $this->applyDiscovery($discoveryDiscovery);

            // Resolve all other discoveries from the container, optionally loading their cache
            $discoveries = array_map(
                fn (string $discoveryClass) => $this->resolveDiscovery($discoveryClass),
                $this->registry->classes,
            );

            // The second pass over all directories to apply all other discovery classes
            $this->discover($discoveries, $discoveryLocations);

            return [$discoveryDiscovery, ...$discoveries];
        } else {
            // Resolve all manually specified discoveries
            $discoveries = array_map(
                fn (string $discoveryClass) => $this->resolveDiscovery($discoveryClass),
                $discoveryClasses,
            );

            $this->discover($discoveries, $discoveryLocations);

            return $discoveries;
        }
    }

    /**
     * Build a list of discovery classes within all registered discovery locations
     * @param Discovery[] $discoveries
     * @param DiscoveryLocation[] $discoveryLocations
     */
    private function discover(array $discoveries, array $discoveryLocations): void
    {
        foreach ($discoveryLocations as $location) {
            if ($this->restoreFromCache($discoveries, $location)) {
                continue;
            }

            $this->scan($location, $discoveries, $location->path);
        }
    }

    private function restoreFromCache(array $discoveries, DiscoveryLocation $location): bool
    {
        if (! $this->isLocationCached($location)) {
            return false;
        }

        $cachedForLocation = $this->discoveryCache->restore($location);

        if (! $this->isCachedLocationUsable($discoveries, $cachedForLocation)) {
            return false;
        }

        foreach ($discoveries as $discovery) {
            $discovery->setItems(
                $discovery->getItems()->addForLocation($location, $cachedForLocation[$discovery::class]),
            );
        }

        return true;
    }

    private function isCachedLocationUsable(array $discoveries, ?array $cachedForLocation): bool
    {
        return (
            is_array($cachedForLocation)
            && array_all(
                array: $discoveries,
                callback: static fn (Discovery $discovery): bool => array_key_exists($discovery::class, $cachedForLocation) && is_iterable($cachedForLocation[$discovery::class]),
            )
        );
    }

    /**
     * Recursively scan a directory and apply a given set of discovery classes to all files
     */
    private function scan(DiscoveryLocation $location, array $discoveries, string $path): void
    {
        $input = Filesystem\normalize_path($path);

        // Make sure the path is valid
        if ($input === null) {
            return;
        }

        // Make sure the path is not marked for skipping
        if ($this->shouldSkipBasedOnConfig($input)) {
            return;
        }

        if (is_file($input)) {
            $this->discoverPath($input, $location, $discoveries);
            return;
        }

        // Make sure the current directory is not marked for skipping
        if ($this->shouldSkipDirectory($input)) {
            return;
        }

        $subPaths = scandir($input, SCANDIR_SORT_NONE);
        if ($subPaths === false) {
            return;
        }

        foreach ($subPaths as $subPath) {
            // `.` and `..` are skipped
            if ($subPath === '.' || $subPath === '..') {
                continue;
            }

            // Scan all files and folders within this directory
            $this->scan($location, $discoveries, "{$input}/{$subPath}");
        }
    }

    private function discoverPath(string $input, DiscoveryLocation $location, array $discoveries): void
    {
        // At this point, we have a single file, let's try and discover it
        $pathInfo = pathinfo($input);
        $extension = $pathInfo['extension'] ?? null;
        $fileName = $pathInfo['filename'] ?: null;

        // If this is a PHP file starting with an uppercase letter, we assume it's a class.
        if ($extension === 'php' && ucfirst($fileName) === $fileName) {
            $className = $location->toClassName($input);

            // Discovery errors (syntax errors, missing imports, etc.)
            // are ignored when they happen in vendor files,
            // but they are allowed to be thrown in project code
            try {
                if ($location->isVendor()) {
                    try {
                        $input = new ClassReflector($className);
                    } catch (Throwable) {
                        // @mago-expect lint:no-empty-catch-clause
                    }
                } elseif (class_exists($className)) {
                    $input = new ClassReflector($className);
                }
            } catch (AssertionError) {
                // Workaround for Pest test files autoloading.
                // @mago-expect lint:no-empty-catch-clause
            }

            if ($input instanceof ClassReflector) {
                $resolvedClassName = $input->getName();

                // Resolve `#[SkipDiscovery]` for this class
                $skipDiscovery = $input->getAttribute(SkipDiscovery::class);

                if ($skipDiscovery !== null && $skipDiscovery->except === []) {
                    $this->shouldSkipForClass[$resolvedClassName] = true;
                } elseif ($skipDiscovery !== null) {
                    foreach ($skipDiscovery->except as $except) {
                        $this->shouldSkipForClass[$resolvedClassName][$except] = true;
                    }
                }

                // Check skipping once again, because at this point we might have converted our path to a class
                if ($this->shouldSkipBasedOnConfig($input)) {
                    return;
                }
            }
        }

        if ($input instanceof ClassReflector) {
            $skipForClass = $this->shouldSkipForClass[$input->getName()] ?? null;

            if ($skipForClass === true) {
                return;
            }

            foreach ($discoveries as $discovery) {
                if (is_array($skipForClass) && ! isset($skipForClass[$discovery::class])) {
                    continue;
                }

                $discovery->discover($location, $input);
            }

            return;
        }

        foreach ($discoveries as $discovery) {
            if ($discovery instanceof DiscoversPath) {
                $discovery->discoverPath($location, $input);
            }
        }
    }

    /**
     * Create a discovery instance from a class name.
     * Optionally set the cached discovery items whenever caching is enabled.
     * @param class-string<Discovery> $discoveryClass
     */
    private function resolveDiscovery(string $discoveryClass): Discovery
    {
        /** @var Discovery $discovery */
        if ($this->container instanceof ContainerInterface || $this->container instanceof Container) {
            $discovery = $this->container->get($discoveryClass);
        } else {
            $discovery = new $discoveryClass();
        }

        $discovery->setItems(new DiscoveryItems());

        return $discovery;
    }

    /**
     * Apply the discovered classes and files.
     */
    private function applyDiscovery(Discovery $discovery): void
    {
        if ($this->appliedDiscovery[$discovery::class] ?? null) {
            return;
        }

        $discovery->apply();

        $this->appliedDiscovery[$discovery::class] = true;
    }

    /**
     * Check whether a path or class should be skipped based on user-provided discovery configuration
     */
    private function shouldSkipBasedOnConfig(ClassReflector|string $input): bool
    {
        if ($input instanceof ClassReflector) {
            $input = $input->getName();
        }

        return $this->discoveryConfig->shouldSkip($input);
    }

    /**
     * Check whether a discovery location should be skipped based on what's cached for a specific discovery class
     */
    private function isLocationCached(DiscoveryLocation $location): bool
    {
        if (! $this->discoveryCache->enabled) {
            return false;
        }

        return match ($this->discoveryCache->strategy) {
            // If discovery cache is disabled, no locations should be skipped, all should always be discovered
            DiscoveryCacheStrategy::NONE, DiscoveryCacheStrategy::INVALID => false,
            // If discover cache is enabled, all locations cache should be skipped
            DiscoveryCacheStrategy::FULL => true,
            // If partial discovery cache is enabled, vendor locations cache should be skipped
            DiscoveryCacheStrategy::PARTIAL => $location->isVendor(),
        };
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
