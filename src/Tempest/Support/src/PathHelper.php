<?php

declare(strict_types=1);

namespace Tempest\Support;

use Exception;
use Tempest\Core\Composer;
use Tempest\Core\Kernel;
use function Tempest\get;

final readonly class PathHelper
{
    public static function root(string ...$paths): string
    {
        return static::make(get(Kernel::class)->root, ...$paths);
    }

    private static function prapareStringForNamespace(string $path, string $root = ''): StringHelper
    {
        $normalized = str($path)
            ->replaceStart($root, '')
            ->replaceStart('/', '')
            ->replace(['/', '//'], '\\');

        // If the path is a to a PHP file, we exclude the file name. Otherwise,
        // it's a path to a directory, which should be included in the namespace.
        if ($normalized->endsWith('.php')) {
            return $normalized->beforeLast(['/', '\\']);
        }

        return $normalized;
    }

    public static function toNamespace(string $path, string $root = ''): string
    {
        $path = static::prapareStringForNamespace($path, $root)->replaceEnd('\\', '');

        return arr(explode('\\', (string) $path))
            ->map(fn (string $segment) => (string) str($segment)->pascal())
            ->implode('\\');
    }

    public static function toRegisteredNamespace(string $path): string
    {
        $composer = get(Composer::class);
        $kernel = get(Kernel::class);

        $relativePath = static::prapareStringForNamespace($path, $kernel->root)
            ->replaceEnd('\\', '')
            ->replace('\\', '/')
            ->finish('/');

        foreach ($composer->namespaces as $namespace) {
            if ($relativePath->startsWith($namespace->path)) {
                return (string) $relativePath
                    ->replace($namespace->path, $namespace->namespace)
                    ->replace(['/', '//'], '\\')
                    ->replaceEnd('.php', '')
                    ->replaceEnd('\\', '');
            }
        }

        throw new Exception(sprintf('No registered namespace matches the specified path [%s].', $path));
    }

    /**
     * Returns a valid path from the specified portions.
     */
    public static function make(string ...$paths): string
    {
        // Split paths items on forward and backward slashes
        $parts = array_reduce($paths, fn (array $carry, string $part) => [...$carry, ...explode('/', $part)], []);
        $parts = array_reduce($parts, fn (array $carry, string $part) => [...$carry, ...explode('\\', $part)], []);

        // Trim forward and backward slashes
        $parts = array_map(fn (string $part) => trim($part, '/\\'), $parts);
        $parts = array_filter($parts);

        // Glue parts together
        $path = implode(DIRECTORY_SEPARATOR, $parts);

        // Add DIRECTORY_SEPARATOR if first entry starts with forward- or backward slash
        $firstEntry = $paths[0];
        if (str_starts_with($firstEntry, '/') || str_starts_with($firstEntry, '\\')) {
            $path = DIRECTORY_SEPARATOR . $path;
        }

        // Add DIRECTORY_SEPARATOR if last entry ends with forward- or backward slash
        $lastEntry = $paths[count($paths) - 1];
        if ((count($paths) > 1 || strlen($lastEntry) > 1) && (str_ends_with($lastEntry, '/') || str_ends_with($lastEntry, '\\'))) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }
}
