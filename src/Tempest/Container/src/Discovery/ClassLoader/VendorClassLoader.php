<?php

namespace Tempest\Container\Discovery\ClassLoader;

use ReflectionClass;

final class VendorClassLoader implements ClassLoader
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = rtrim($rootDir, '/\\');
    }

    public function load(): array
    {
        $packages = $this->findPackagesDependentOnTempest();

        // Early bail if there are no packages.
        if ($packages === []) {
            return [];
        }

        // Now we start to build our reflection classes.
        $classMap = include $this->rootDir . '/vendor/composer/autoload_classmap.php';
        $classes = [];

        foreach ($packages as $name => $namespaces) {
            foreach ($classMap as $class => $path) {
                foreach ($namespaces as $namespace) {
                    if (str_starts_with($class, $namespace)) {
                        $classes[] = new ReflectionClass($class);
                    }
                }
            }
        }

        return $classes;
    }

    private function findPackagesDependentOnTempest(): array
    {
        // TODO: This could probably be cached as well.
        $composerLock = json_decode(file_get_contents($this->rootDir . '/composer.lock'), true);
        $packages = array_merge($composerLock['packages'] ?? [], $composerLock['packages-dev'] ?? []);

        $dependentPackages = [];

        foreach ($packages as $package) {
            $dependencies = array_merge(
                $package['require'] ?? [],
                $package['require-dev'] ?? []
            );

            foreach ($dependencies as $dependency => $version) {
                if (str_starts_with($dependency, 'tempest')) {
//                    $dependentPackages[$package['name']] = $this->rootDir . '/vendor/' . $package['name'];
                    $dependentPackages[$package['name']] = $this->getPackageNamespaces($package);

                    break;
                }
            }
        }

        return $dependentPackages;
    }

    private function getPackageNamespaces(array $package): array
    {
        $namespaces = [];

        foreach ($package['autoload'] as $autoloadType => $autoloadNamespaces) {
            foreach ($autoloadNamespaces as $autoloadNamespace => $autoloadDir) {
                $namespaces[] = $autoloadNamespace;
            }
        }

        return $namespaces;
    }
}