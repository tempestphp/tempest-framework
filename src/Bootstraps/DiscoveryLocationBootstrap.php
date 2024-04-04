<?php

declare(strict_types=1);

namespace Tempest\Bootstraps;

use Tempest\CoreConfig;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Kernel;
use Tempest\Support\PathHelper;

final readonly class DiscoveryLocationBootstrap implements Bootstrap
{
    public function __construct(
        private CoreConfig $coreConfig,
        private Kernel $kernel,
    ) {
    }

    public function boot(): void
    {
        $discoveredLocations = [
            ...$this->discoverAppNamespaces(),
            ...$this->discoverInstalledPackageLocations(),
        ];
        
        $this->addDiscoveryLocations($discoveredLocations);
    }

    private function discoverInstalledPackageLocations(): array
    {
        $composerPath = PathHelper::make($this->coreConfig->root, 'vendor/composer');
        $installed = $this->loadJsonFile(PathHelper::make($composerPath, 'installed.json'));
        $packages = $installed['packages'] ?? [];

        $discoveredLocations = [];

        foreach ($packages as $package) {
            $packagePath = PathHelper::make($composerPath, $package['install-path'] ?? '');
            $requiresTempest = isset($package['require']['tempest/framework']);
            $hasPsr4Namespaces = isset($package['autoload']['psr-4']);
            $packageName = ($package['name'] ?? null);
            $isTempest = $packageName === 'tempest/framework'
                || $packageName === 'tempest/core';

            if (($requiresTempest && $hasPsr4Namespaces) || $isTempest) {
                foreach ($package['autoload']['psr-4'] as $namespace => $namespacePath) {
                    $namespacePath = PathHelper::make($packagePath, $namespacePath);

                    $discoveredLocations[] = [
                        'namespace' => $namespace,
                        'path' => $namespacePath,
                    ];
                }
            }
        }

        return $discoveredLocations;
    }

    private function discoverAppNamespaces(): array
    {
        $composer = $this->loadJsonFile(PathHelper::make($this->coreConfig->root, 'composer.json'));
        $namespaceMap = $composer['autoload']['psr-4'] ?? [];

        $discoveredLocations = [];

        foreach ($namespaceMap as $namespace => $path) {
            $path = PathHelper::make($this->coreConfig->root, $path);

            $discoveredLocations[] = [
                'namespace' => $namespace,
                'path' => $path,
            ];
        }

        return $discoveredLocations;
    }

    private function addDiscoveryLocations(array $discoveredLocations): void
    {
        foreach ($discoveredLocations as $location) {
            $this->coreConfig->discoveryLocations = [new DiscoveryLocation(...$location), ...$this->coreConfig->discoveryLocations];
        }
    }

    private function loadJsonFile(string $path): array
    {
        if (! is_file($path)) {
            $relativePath = str_replace($this->coreConfig->root, '.', $path);

            throw new BootstrapException(sprintf('Could not locate %s, try running "composer install"', $relativePath));
        }

        return json_decode(file_get_contents($path), true);
    }
}
