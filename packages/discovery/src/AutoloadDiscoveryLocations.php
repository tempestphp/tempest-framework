<?php

declare(strict_types=1);

namespace Tempest\Discovery;

use Tempest\Support\Filesystem;
use Tempest\Support\Path;

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
        $composerPath = Path\normalize($this->rootPath, 'vendor/composer');
        $installed = $this->loadJsonFile(Path\normalize($composerPath, 'installed.json'));
        $packages = $installed['packages'] ?? [];

        return [
            ...$this->discoverInstalledPackages($composerPath, $packages),
            ...$this->discoverAppNamespaces(),
        ];
    }

    /** @return DiscoveryLocation[] */
    private function discoverInstalledPackages(string $composerPath, array $packages): array
    {
        $core = [];
        $vendor = [];
        $optIn = [];

        foreach ($packages as $package) {
            if (! isset($package['autoload']['psr-4'])) {
                continue;
            }

            $packagePath = Path\normalize($composerPath, $package['install-path'] ?? '');

            if (str_starts_with($package['name'] ?? '', needle: 'tempest/')) {
                $core = [...$core, ...$this->discoverPackageLocations($packagePath, $package['autoload']['psr-4'])];
                continue;
            }

            if (array_find($package['require'] ?? [], static fn ($_, string $package) => str_starts_with($package, needle: 'tempest/'))) {
                $vendor = [...$vendor, ...$this->discoverPackageLocations($packagePath, $package['autoload']['psr-4'])];
                continue;
            }

            if ($package['extra']['tempest']['can-discover'] ?? false) {
                $optIn = [...$optIn, ...$this->discoverPackageLocations($packagePath, $package['autoload']['psr-4'], $package['extra']['tempest']['ignore'] ?? [])];
                continue;
            }
        }

        return [...$core, ...$vendor, ...$optIn];
    }

    /**
     * @return DiscoveryLocation[]
     */
    private function discoverAppNamespaces(): array
    {
        $discoveredLocations = [];

        foreach ($this->composer->namespaces as $namespace) {
            $path = Path\normalize($this->rootPath, $namespace->path);

            $discoveredLocations[] = new DiscoveryLocation($namespace->namespace, $path);
        }

        return $discoveredLocations;
    }

    /** @return DiscoveryLocation[] */
    private function discoverPackageLocations(string $packagePath, array $psr4Namespaces, array $ignore = []): array
    {
        $discoveredLocations = [];
        $ignore = array_map(static fn (string $path) => Filesystem\normalize_path(Path\normalize($packagePath, $path)), $ignore);

        foreach ($psr4Namespaces as $namespace => $namespacePath) {
            if (is_array($namespacePath)) {
                foreach ($namespacePath as $path) {
                    if (! is_string($path)) {
                        continue;
                    }

                    $discoveredLocations[] = new DiscoveryLocation($namespace, Path\normalize($packagePath, $path), $ignore);
                }

                continue;
            }

            if (is_string($namespacePath)) {
                $discoveredLocations[] = new DiscoveryLocation($namespace, Path\normalize($packagePath, $namespacePath), $ignore);
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
