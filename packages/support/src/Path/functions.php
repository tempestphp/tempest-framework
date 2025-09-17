<?php

declare(strict_types=1);

namespace Tempest\Support\Path {
    use Stringable;

    use function Tempest\Support\Regex\matches;
    use function Tempest\Support\Str\ends_with;
    use function Tempest\Support\Str\starts_with;

    /**
     * Determines whether the given path is a relative path. The path is not checked against the filesystem.
     */
    function is_relative_path(null|Stringable|string ...$parts): bool
    {
        return ! namespace\is_absolute_path(...$parts);
    }

    /**
     * Converts the given absolute path to a path relative to `$from`.
     * If the given path is not an absolute path, it is assumed to already by relative to `$from` and will be returned as-is.
     */
    function to_relative_path(null|Stringable|string $from, Stringable|string ...$parts): string
    {
        $path = namespace\normalize(...$parts);
        $from = $from === null ? '' : (string) $from;

        if (is_relative_path($path)) {
            return $path;
        }

        $from = rtrim($from, '/');
        $path = rtrim($path, '/');

        $fromParts = explode('/', $from);
        $pathParts = explode('/', $path);

        while ($fromParts !== [] && $pathParts !== [] && $fromParts[0] === $pathParts[0]) {
            array_shift($fromParts);
            array_shift($pathParts);
        }

        $upDirs = count($fromParts);
        $relativePath = str_repeat('../', $upDirs) . implode('/', $pathParts);

        return $relativePath === '' ? '.' : $relativePath;
    }

    /**
     * Determines whether the given path is an absolute path. The path is not checked against the filesystem.
     */
    function is_absolute_path(null|Stringable|string ...$parts): bool
    {
        $path = namespace\normalize(...$parts);

        if (strlen($path) === 0 || '.' === $path[0]) {
            return false;
        }

        if (preg_match('#^[a-zA-Z]:/#', $path)) {
            return true;
        }

        return '/' === $path[0];
    }

    /**
     * Converts the given path to an absolute path.
     */
    function to_absolute_path(Stringable|string $cwd, null|Stringable|string ...$parts): string
    {
        $cwd = namespace\normalize($cwd);
        $path = namespace\normalize(...$parts);

        if (starts_with($path, $cwd) && namespace\is_absolute_path($path)) {
            return $path;
        }

        $segments = explode('/', namespace\normalize($cwd, $path));
        $resolved = [];

        foreach ($segments as $part) {
            if ($part === '') {
                continue;
            }

            if ($part === '.') {
                continue;
            }

            if ($part === '..') {
                if ($resolved !== []) {
                    array_pop($resolved);
                }
            } else {
                $resolved[] = $part;
            }
        }

        $absolutePath = namespace\normalize(...$resolved);

        if (matches($cwd, '#^[a-zA-Z]:/#')) {
            return $absolutePath;
        }

        return '/' . $absolutePath;
    }

    /**
     * Normalizes the given path to use forward-slashes.
     */
    function normalize(null|Stringable|string ...$paths): string
    {
        if ($paths === []) {
            return '';
        }

        $paths = array_map(
            fn (null|Stringable|string $path) => $path === null ? '' : (string) $path,
            $paths,
        );

        // Split paths items on forward and backward slashes
        $parts = array_reduce($paths, fn (array $carry, string $part) => [...$carry, ...explode('/', $part)], []);
        $parts = array_reduce($parts, fn (array $carry, string $part) => [...$carry, ...explode('\\', $part)], []);

        // Trim forward and backward slashes
        $parts = array_map(fn (string $part) => trim($part, '/\\'), $parts);
        $parts = array_filter($parts);

        // Glue parts together
        $path = implode('/', $parts);

        // Add / if first entry starts with forward- or backward slash
        $firstEntry = $paths[0];

        if (starts_with($firstEntry, ['/', '\\'])) {
            $path = '/' . $path;
        }

        // Add / if last entry ends with forward- or backward slash
        $lastEntry = $paths[count($paths) - 1];

        if ((count($paths) > 1 || strlen($lastEntry) > 1) && ends_with($lastEntry, ['/', '\\'])) {
            $path .= '/';
        }

        return $path;
    }
}
