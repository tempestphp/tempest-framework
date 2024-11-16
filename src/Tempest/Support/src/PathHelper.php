<?php

declare(strict_types=1);

namespace Tempest\Support;

use Exception;
use Stringable;
use Tempest\Core\Composer;
use Tempest\Core\Kernel;
use function Tempest\get;
use function Tempest\src_namespace;
use function Tempest\src_path;

final readonly class PathHelper implements Stringable
{
    private string $path;

    public function __construct(Stringable|string ...$paths)
    {
        $paths = array_map(
            fn (self|string $path) => (string) $path,
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

        if (str_starts_with($firstEntry, '/') || str_starts_with($firstEntry, '\\')) {
            $path = '/' . $path;
        }

        // Add / if last entry ends with forward- or backward slash
        $lastEntry = $paths[count($paths) - 1];

        if ((count($paths) > 1 || strlen($lastEntry) > 1) && (str_ends_with($lastEntry, '/') || str_ends_with($lastEntry, '\\'))) {
            $path .= '/';
        }

        $this->path = $path;
    }

    private static function prepareStringForNamespace(string $path, string $root = ''): StringHelper
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
        $path = static::prepareStringForNamespace($path, $root)->replaceEnd('\\', '');

        return arr(explode('\\', (string) $path))
            ->map(fn (string $segment) => (string) str($segment)->pascal())
            ->implode('\\')
            ->toString();
    }

    public static function toMainNamespace(string $path): string
    {
        return self::toNamespace(
            src_namespace() . '/' . str($path)
                ->replaceStart(src_path(), '')
                ->trim('/')
                ->toString()
        );
    }

    public static function toRegisteredNamespace(string $path): string
    {
        $composer = get(Composer::class);
        $kernel = get(Kernel::class);

        $relativePath = static::prepareStringForNamespace($path, $kernel->root)
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
     * Convert a path to a class name.
     *
     * @param string $path The path to convert.
     */
    public static function toClassName(string $path): string
    {
        return str($path)
            ->replace(['/', '\\'], '/')
            ->replaceEnd('/', '')
            ->replaceEnd('.php', '')
            ->afterLast('/')
            ->classBasename()
            ->toString();
    }

    public function toString(): string
    {
        return $this->path;
    }

    public function info(int $flags = PATHINFO_ALL): string|array
    {
        return pathinfo($this->path, $flags);
    }

    public function path(): string
    {
        return $this->path;
    }

    public function dirname(): string
    {
        return $this->info(PATHINFO_DIRNAME);
    }

    public function filename(): string
    {
        return $this->info(PATHINFO_FILENAME);
    }

    public function basename(): string
    {
        return $this->info(PATHINFO_BASENAME);
    }

    public function extension(): string
    {
        return $this->info(PATHINFO_EXTENSION);
    }

    public function glob(string $pattern): ArrayHelper
    {
        return arr(
            glob((new self($this->path, $pattern))->toString())
        );
    }

    public function isDirectory(): bool
    {
        return is_dir($this->path);
    }

    public function isFile(): bool
    {
        return is_file($this->path);
    }

    public function exists(): bool
    {
        return file_exists($this->path);
    }

    public function equals(Stringable $other): bool
    {
        return $this->path === (string) $other;
    }

    public function __toString(): string
    {
        return $this->path;
    }
}
