<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Tempest\Support\Filesystem;

use function Tempest\Support\Path\normalize;

final readonly class AutoloadDiscoveryLocations
{
    private Composer $composer;

    public function __construct(
        private string $rootPath,
        ?Composer $composer = null,
    ) {
        if (! $composer instanceof Composer) {
            $composer = new Composer($rootPath);
            $composer->load();
        }

        $this->composer = $composer;
    }

    /** @return \Tempest\Discovery\DiscoveryLocation[] */
    public function __invoke(): array
    {
        return [
            ...$this->discoverCorePackages(),
            ...$this->discoverVendorPackages(),
            ...$this->discoverAppNamespaces(),
        ];
    }

    /**
     * @return DiscoveryLocation[]
     */
    private function discoverCorePackages(): array
    {
        $composerPath = normalize($this->rootPath, 'vendor/composer');
        $installed = $this->loadJsonFile(normalize($composerPath, 'installed.json'));
        $packages = $installed['packages'] ?? [];

        $discoveredLocations = [];

        foreach ($packages as $package) {
            $packageName = $package['name'] ?? '';
            $isTempest = str_starts_with($packageName, 'tempest');

            if (! $isTempest) {
                continue;
            }

            $packagePath = normalize($composerPath, $package['install-path'] ?? '');

            foreach ($package['autoload']['psr-4'] as $namespace => $namespacePath) {
                $namespacePath = normalize($packagePath, $namespacePath);

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
        $discoveredLocations = [];

        foreach ($this->composer->namespaces as $namespace) {
            $path = normalize($this->rootPath, $namespace->path);

            $discoveredLocations[] = new DiscoveryLocation($namespace->namespace, $path);
        }

        return $discoveredLocations;
    }

    /**
     * @return DiscoveryLocation[]
     */
    private function discoverVendorPackages(): array
    {
        $composerPath = normalize($this->rootPath, 'vendor/composer');
        $installed = $this->loadJsonFile(normalize($composerPath, 'installed.json'));
        $packages = $installed['packages'] ?? [];

        $discoveredLocations = [];

        foreach ($packages as $package) {
            $packageName = $package['name'] ?? '';
            $isTempest = str_starts_with($packageName, 'tempest');

            if ($isTempest) {
                continue;
            }

            $packagePath = normalize($composerPath, $package['install-path'] ?? '');
            $requiresTempest = isset($package['require']['tempest/framework']) || isset($package['require']['tempest/core']);
            $hasPsr4Namespaces = isset($package['autoload']['psr-4']);

            if (! ($requiresTempest && $hasPsr4Namespaces)) {
                continue;
            }

            foreach ($package['autoload']['psr-4'] as $namespace => $namespacePath) {
                $path = normalize($packagePath, $namespacePath);

                $discoveredLocations[] = new DiscoveryLocation($namespace, $path);
            }
        }

        return $discoveredLocations;
    }

    private function loadJsonFile(string $path): array
    {
        if (! Filesystem\is_file($path)) {
            throw new DiscoveryLocationCouldNotBeLoaded($path);
        }

        return Filesystem\read_json($path);
    }
}
