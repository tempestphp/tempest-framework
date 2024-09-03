<?php

declare(strict_types=1);

namespace Tempest\Core\Bootstraps;

use Tempest\Core\AppConfig;
use Tempest\Core\DiscoveryLocation;
use Tempest\Support\PathHelper;

final readonly class DiscoveryLocationBootstrap implements Bootstrap
{
    public function __construct(
        private AppConfig $appConfig,
    ) {
    }

    public function boot(): void
    {
        $discoveredLocations = [
            ...$this->discoverCorePackages(),
            ...$this->discoverAppNamespaces(),
            ...$this->discoverVendorPackages(),
        ];

        $this->addDiscoveryLocations($discoveredLocations);
    }

    /**
     * @return DiscoveryLocation[]
     */
    private function discoverCorePackages(): array
    {
        $composerPath = PathHelper::make($this->appConfig->root, 'vendor/composer');
        $installed = $this->loadJsonFile(PathHelper::make($composerPath, 'installed.json'));
        $packages = $installed['packages'] ?? [];

        $discoveredLocations = [];

        foreach ($packages as $package) {
            $packageName = ($package['name'] ?? '');
            $isTempest = str_starts_with($packageName, 'tempest');

            if (! $isTempest) {
                continue;
            }

            $packagePath = PathHelper::make($composerPath, $package['install-path'] ?? '');

            foreach ($package['autoload']['psr-4'] as $namespace => $namespacePath) {
                $namespacePath = PathHelper::make($packagePath, $namespacePath);

                $discoveredLocations[] = new DiscoveryLocation($namespace, $namespacePath);
            }
        }

        return $discoveredLocations;
    }

    /**
     * @return DiscoveryLocation[]
     */
    private function discoverAppNamespaces(): array
    {
        $composer = $this->loadJsonFile(PathHelper::make($this->appConfig->root, 'composer.json'));
        $namespaceMap = $composer['autoload']['psr-4'] ?? [];

        $discoveredLocations = [];

        foreach ($namespaceMap as $namespace => $path) {
            $path = PathHelper::make($this->appConfig->root, $path);

            $discoveredLocations[] = new DiscoveryLocation($namespace, $path);
        }

        return $discoveredLocations;
    }

    /**
     * @return DiscoveryLocation[]
     */
    private function discoverVendorPackages(): array
    {
        $composerPath = PathHelper::make($this->appConfig->root, 'vendor/composer');
        $installed = $this->loadJsonFile(PathHelper::make($composerPath, 'installed.json'));
        $packages = $installed['packages'] ?? [];

        $discoveredLocations = [];

        foreach ($packages as $package) {
            $packageName = ($package['name'] ?? '');
            $isTempest = str_starts_with($packageName, 'tempest');

            if ($isTempest) {
                continue;
            }

            $packagePath = PathHelper::make($composerPath, $package['install-path'] ?? '');
            $requiresTempest = isset($package['require']['tempest/framework']) || isset($package['require']['tempest/core']);
            $hasPsr4Namespaces = isset($package['autoload']['psr-4']);

            if (! ($requiresTempest && $hasPsr4Namespaces)) {
                continue;
            }

            foreach ($package['autoload']['psr-4'] as $namespace => $namespacePath) {
                $path = PathHelper::make($packagePath, $namespacePath);

                $discoveredLocations[] = new DiscoveryLocation($namespace, $path);
            }
        }

        return $discoveredLocations;
    }

    private function addDiscoveryLocations(array $discoveredLocations): void
    {
        $this->appConfig->discoveryLocations = [
            ...$discoveredLocations,
            ...$this->appConfig->discoveryLocations,
        ];
    }

    private function loadJsonFile(string $path): array
    {
        if (! file_exists($path)) {
            $relativePath = str_replace($this->appConfig->root, '.', $path);

            throw new BootstrapException(sprintf('Could not locate %s, try running "composer install"', $relativePath));
        }

        return json_decode(file_get_contents($path), true);
    }
}
