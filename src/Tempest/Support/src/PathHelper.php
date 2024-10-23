<?php

declare(strict_types=1);

namespace Tempest\Support;

final readonly class PathHelper
{
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
