<?php

declare(strict_types=1);

namespace Tempest\Support;

use Exception;
use Tempest\Core\Composer;
use Tempest\Core\Kernel;
use function Tempest\get;
use function Tempest\src_namespace;
use function Tempest\src_path;

final readonly class NamespaceHelper
{
    public static function toNamespace(string $path, string $root = ''): string
    {
        $path = self::prepareStringForNamespace($path, $root)->replaceEnd('\\', '');

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

        $relativePath = self::prepareStringForNamespace($path, $kernel->root)
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
}
