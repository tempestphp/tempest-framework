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
use Throwable;

/** @internal */
final class LoadDiscoveryClasses
{
    private array $appliedDiscovery = [];

    public function __construct(
        private readonly Kernel $kernel,
        private readonly Container $container,
        private readonly DiscoveryConfig $discoveryConfig,
        private readonly DiscoveryCache $discoveryCache,
    ) {}

    public function __invoke(): void
    {
        $discoveries = $this->build();

        foreach ($discoveries as $discovery) {
            $this->applyDiscovery($discovery);
        }
    }

    /** @return Discovery[] */
    public function build(): array
    {
        // DiscoveryDiscovery needs to be applied before we can build all other discoveries
        $discoveryDiscovery = $this->buildDiscovery(DiscoveryDiscovery::class);
        $this->applyDiscovery($discoveryDiscovery);

        $builtDiscovery = [$discoveryDiscovery];

        foreach ($this->kernel->discoveryClasses as $discoveryClass) {
            $discovery = $this->buildDiscovery($discoveryClass);
            $builtDiscovery[] = $discovery;
        }

        return $builtDiscovery;
    }

    /**
     * Create a discovery instance from a class name.
     * Optionally set the cached discovery items whenever caching is enabled.
     */
    private function resolveDiscovery(string $discoveryClass): Discovery
    {
        /** @var Discovery $discovery */
        $discovery = $this->container->get($discoveryClass);

        if ($this->discoveryCache->enabled) {
            $discovery->setItems(
                $this->discoveryCache->restore($discoveryClass) ?? new DiscoveryItems(),
            );
        } else {
            $discovery->setItems(new DiscoveryItems());
        }

        return $discovery;
    }

    /**
     * Build one specific discovery instance.
     */
    private function buildDiscovery(string $discoveryClass): Discovery
    {
        $discovery = $this->resolveDiscovery($discoveryClass);

        if ($this->discoveryCache->strategy === DiscoveryCacheStrategy::FULL && $discovery->getItems()->isLoaded()) {
            return $discovery;
        }

        foreach ($this->kernel->discoveryLocations as $location) {
            $this->discoverPath($discovery, $location, $location->path);
        }

        return $discovery;
    }

    private function discoverPath(Discovery $discovery, DiscoveryLocation $location, string $path): void
    {
        if ($this->shouldSkipLocation($location)) {
            return;
        }

        $input = realpath($path);

        if ($input === false) {
            return;
        }

        // Make sure the path is not marked for skipping
        if ($this->shouldSkipBasedOnConfig($input)) {
            return;
        }

        // Directories are scanned recursively
        if (is_dir($input)) {
            if ($this->shouldSkipDirectory($input)) {
                return;
            }

            foreach (scandir($input) as $subPath) {
                if ($subPath === '.' || $subPath === '..') {
                    continue;
                }

                $this->discoverPath($discovery, $location, "{$input}/{$subPath}");
            }

            return;
        }

        $pathInfo = pathinfo($input);
        $extension = $pathInfo['extension'] ?? null;
        $fileName = $pathInfo['filename'] ?: null;

        // We assume that any PHP file that starts with an uppercase letter will be a class
        if ($extension === 'php' && ucfirst($fileName) === $fileName) {
            $className = $location->toClassName($input);

            // Discovery errors (syntax errors, missing imports, etc.)
            // are ignored when they happen in vendor files,
            // but they are allowed to be thrown in project code
            if ($location->isVendor()) {
                try {
                    $input = new ClassReflector($className);
                } catch (Throwable $e) { // @mago-expect best-practices/no-empty-catch-clause
                }
            } elseif (class_exists($className)) {
                $input = new ClassReflector($className);
            }
        }

        // If the input is a class, we'll try to discover it
        if ($input instanceof ClassReflector) {
            // Check whether the class should be skipped
            if ($this->shouldSkipBasedOnConfig($input)) {
                return;
            }

            // Check whether this class is marked with `#[SkipDiscovery]`
            if ($this->shouldSkipDiscoveryForClass($discovery, $input)) {
                return;
            }

            $discovery->discover($location, $input);
        } elseif ($discovery instanceof DiscoversPath) {
            // If the input is NOT a class, AND the discovery class can discover paths, we'll call `discoverPath`
            // Note that we've already checked whether the path was marked for skipping earlier in this method
            $discovery->discoverPath($location, $input);
        }
    }

    /**
     * Apply the discovered classes and files. Also store the discovered items into cache, if caching is enabled
     */
    private function applyDiscovery(Discovery $discovery): void
    {
        if ($this->appliedDiscovery[$discovery::class] ?? null) {
            return;
        }

        $discovery->apply();

        $this->appliedDiscovery[$discovery::class] = true;
    }

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
        $attribute = $input->getAttribute(SkipDiscovery::class);

        if ($attribute === null) {
            return false;
        }

        return ! in_array($discovery::class, $attribute->except, strict: true);
    }

    /**
     * Check whether a discovery location should be skipped based on what's cached for a specific discovery class
     */
    private function shouldSkipLocation(DiscoveryLocation $location): bool
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

        return $directory === 'node_modules';
    }
}
