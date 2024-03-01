<?php

declare(strict_types=1);

namespace Tempest\Bootstraps;

use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Container\Container;
use Tempest\Discovery\DiscoveryLocation;
use function Tempest\path;

final readonly class DiscoveryLocationBootstrap implements Bootstrap
{
    private string $root;

    public function __construct(
        private Container $container,
    ) {
        $this->root = $this->container->get(Kernel::class)->root;
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
        $composerPath = path($this->root, 'vendor/composer');
        $installed = $this->loadJsonFile(path($composerPath, 'installed.json'));
        $packages = $installed['packages'] ?? [];

        $discoveredLocations = [];

        foreach ($packages as $package) {
            $packagePath = path($composerPath, $package['install-path']);
            $requiresTempest = isset($package['require']['tempest/framework']);
            $hasPsr4Namespaces = isset($package['autoload']['psr-4']);
            $isTempest = ($package['name'] ?? null) === 'tempest/framework';

            if (($requiresTempest && $hasPsr4Namespaces) || $isTempest) {
                foreach ($package['autoload']['psr-4'] as $namespace => $namespacePath) {
                    $namespacePath = path($packagePath, $namespacePath);

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
        $composer = $this->loadJsonFile(path($this->root, 'composer.json'));
        $namespaceMap = $composer['autoload']['psr-4'] ?? [];

        $discoveredLocations = [];

        foreach ($namespaceMap as $namespace => $path) {
            $path = path($this->root, $path);

            $discoveredLocations[] = [
                'namespace' => $namespace,
                'path' => $path,
            ];
        }

        return $discoveredLocations;
    }

    private function addDiscoveryLocations(array $discoveredLocations): void
    {
        foreach ($discoveredLocations as &$location) {
            $location = new DiscoveryLocation(...$location);
        }

        unset($location);

        $this->container->get(AppConfig::class)->discoveryLocations = $discoveredLocations;
    }

    private function loadJsonFile(string $path): array
    {
        if (! is_file($path)) {
            $relativePath = str_replace($this->root, '.', $path);

            throw new BootstrapException(sprintf('Could not locate %s, try running "composer install"', $relativePath));
        }

        return json_decode(file_get_contents($path), true);
    }
}
