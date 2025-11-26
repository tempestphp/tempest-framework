<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use Tempest\Container\Container;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\DiscoveryCacheStrategy;
use Tempest\Core\DiscoveryConfig;
use Tempest\Core\DiscoveryDiscovery;
use Tempest\Core\Kernel;
use Tempest\Discovery\DiscoversPath;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryItems;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Filesystem;
use Throwable;

/** @internal */
final class LoadDiscoveryClasses
{
    private array $appliedDiscovery = [];
    private array $shouldSkipForClass = [];

    public function __construct(
        private readonly Container $container,
        private readonly DiscoveryConfig $discoveryConfig,
        private readonly DiscoveryCache $discoveryCache,
    ) {}

    /**
     * @param class-string<Discovery>[]|null $discoveryClasses
     * @param DiscoveryLocation[]|null $discoveryLocations
     */
    public function __invoke(
        ?array $discoveryClasses = null,
        ?array $discoveryLocations = null,
    ): void {
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
    ): array {
        $kernel = $this->container->get(Kernel::class);

        $discoveryLocations ??= $kernel->discoveryLocations;

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
                $kernel->discoveryClasses,
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
     * @param DiscoveryLocation[]|null $discoveryLocations
     */
    private function discover(array $discoveries, array $discoveryLocations): void
    {
        foreach ($discoveryLocations as $location) {
            // Skip location based on cache status
            if ($this->isLocationCached($location)) {
                $cachedForLocation = $this->discoveryCache->restore($location);

                // Merge discovery items
                foreach ($discoveries as $discovery) {
                    $itemsForDiscovery = $cachedForLocation[$discovery::class] ?? null;

                    if (! $itemsForDiscovery) {
                        continue;
                    }

                    $discovery->setItems(
                        $discovery->getItems()->addForLocation($location, $itemsForDiscovery),
                    );
                }

                continue;
            }

            // Scan all files within this location
            $this->scan(
                location: $location,
                discoveries: $discoveries,
                path: $location->path,
            );
        }
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
                $this->scan($location, $discoveries, "{$input}/{$subPath}");
            }

            return;
        }

        // At this point, we have a single file, let's try and discover it
        $pathInfo = pathinfo($input);
        $extension = $pathInfo['extension'] ?? null;
        $fileName = $pathInfo['filename'] ?: null;

        // If this is a PHP file starting with an uppercase letter, we assume it's a class.
        // TODO: Figure out if we can refactor this to checking composer's autoload map (it might not always be available)
        //       An other idea is to check whether composer has a check to verify whether a file is a class?
        if ($extension === 'php' && ucfirst($fileName) === $fileName) {
            $className = $location->toClassName($input);

            // Discovery errors (syntax errors, missing imports, etc.)
            // are ignored when they happen in vendor files,
            // but they are allowed to be thrown in project code
            if ($location->isVendor()) {
                try {
                    $input = new ClassReflector($className);
                } catch (Throwable) { // @mago-expect lint:no-empty-catch-clause
                }
            } elseif (class_exists($className)) {
                $input = new ClassReflector($className);
            }

            if ($input instanceof ClassReflector) {
                // Resolve `#[SkipDiscovery]` for this class
                $skipDiscovery = $input->getAttribute(SkipDiscovery::class);

                if ($skipDiscovery !== null && $skipDiscovery->except === []) {
                    $this->shouldSkipForClass[$className] = true;
                } elseif ($skipDiscovery !== null) {
                    foreach ($skipDiscovery->except as $except) {
                        $this->shouldSkipForClass[$className][$except] = true;
                    }
                }

                // Check skipping once again, because at this point we might have converted our path to a class
                if ($this->shouldSkipBasedOnConfig($input)) {
                    return;
                }
            }
        }

        // Pass the current file to each discovery class
        foreach ($discoveries as $discovery) {
            // If the input is a class, we'll try to discover it
            if ($input instanceof ClassReflector) {
                // Check whether this class is marked with `#[SkipDiscovery]`
                if ($this->shouldSkipDiscoveryForClass($discovery, $input)) {
                    continue;
                }

                $discovery->discover($location, $input);
            } elseif ($discovery instanceof DiscoversPath) {
                // If the input is NOT a class, AND the discovery class can discover paths, we'll call `discoverPath`
                // Note that we've already checked whether the path was marked for skipping earlier in this method
                $discovery->discoverPath($location, $input);
            }
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
     * Check whether discovery for a specific class should be skipped based on the #[SkipDiscovery] attribute
     */
    private function shouldSkipDiscoveryForClass(Discovery $discovery, ClassReflector $input): bool
    {
        // There's no `#[SkipDiscovery]` attribute, so the class shouldn't be skipped
        if (! isset($this->shouldSkipForClass[$input->getName()])) {
            return false;
        }

        // The class has a general `#[SkipDiscovery]` attribute without exceptions
        if ($this->shouldSkipForClass[$input->getName()] === true) {
            return true;
        }

        // Current discovery is not added as "except", so it should be skipped
        if (! isset($this->shouldSkipForClass[$input->getName()][$discovery::class])) {
            return true;
        }

        // Current discovery was present in the excepted array, so it shouldn't be skipped
        return false;
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
