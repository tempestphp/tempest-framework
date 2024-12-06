<?php

namespace Tempest\Container\Discovery\ClassLoader;

use ReflectionClass;

final class AppClassLoader implements ClassLoader
{
    private string $rootDir;

    public function __construct(string $rootDir)
    {
        $this->rootDir = rtrim($rootDir, '/\\');
    }

    public function load(): array
    {
        $namespaces = $this->findAutoloadNamespaces();

        // Early bail if there are no namespaces.
        if ($namespaces === []) {
            return [];
        }

        // Now we start to build our reflection classes.
        $classMap = include $this->rootDir . '/vendor/composer/autoload_classmap.php';
        $classes = [];

        foreach ($classMap as $class => $path) {
            foreach ($namespaces as $namespace => $path) {
                if (str_starts_with($class, $namespace)) {
                    $classes[] = new ReflectionClass($class);
                }
            }
        }

        return $classes;
    }

    private function findAutoloadNamespaces(): array
    {
        // TODO: This could probably be cached as well.
        $composer = json_decode(file_get_contents($this->rootDir . '/composer.json'), true);
        $autoload = array_merge($composer['autoload'] ?? [], $composer['autoload-dev'] ?? []);
        $namespaces = [];

        foreach ($autoload as $autoloadType => $autoloadNamespaces) {
            // TODO: I'm sure this needs more.
            if ($autoloadType === 'files') {
                continue;
            }

            $namespaces = array_merge($namespaces, $autoloadNamespaces);
        }

        return $namespaces;
    }
}