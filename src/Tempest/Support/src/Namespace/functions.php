<?php

declare(strict_types=1);

namespace Tempest\Support\Namespace {
    use Tempest\Core\Composer;
    use Tempest\Core\Kernel;
    use Tempest\Support\Str\ImmutableString;
    use function Tempest\get;
    use function Tempest\src_namespace;
    use function Tempest\src_path;
    use function Tempest\Support\arr;
    use function Tempest\Support\str;

    /**
     * Converts the given file system path to the equivalent PSR-4 namespace.
     *
     * ## Example
     * ```php
     * to_namespace('app/Auth/User.php'); // App\Auth
     * to_namespace('app/Auth/User'); // App\Auth\User
     * ```
     */
    function to_namespace(string $path, string $root = ''): string
    {
        $path = prepare_namespace($path, $root)
            ->replaceEnd('\\', '');

        return arr(explode('\\', (string) $path))
            ->map(fn (string $segment) => (string) str($segment)->pascal())
            ->implode('\\')
            ->toString();
    }

    /**
     * Converts the given file system path to the equivalent PSR-4 namespace within the application's main namespace.
     *
     * ## Example
     * ```php
     * // Main namespace is `App`, with path `app/`.
     * to_main_namespace('app/Auth/User.php'); // App\Auth
     * to_main_namespace('app/Auth/User'); // App\Auth\User
     * ```
     */
    function to_main_namespace(string $path): string
    {
        return to_namespace(
            src_namespace() . '/' . str($path)
                ->replaceStart(src_path(), '')
                ->trim('/')
                ->toString(),
        );
    }

    /**
     * Converts a file system path to a namespace registered in the project's `composer.json`.
     * If the resulting PSR-4 namespace does not match any registered namespace, an exception is thrown.
     */
    function to_registered_namespace(string $path): string
    {
        $composer = get(Composer::class);
        $kernel = get(Kernel::class);

        $relativePath = prepare_namespace($path, $kernel->root)
            ->stripEnd('\\')
            ->replace('\\', '/')
            ->finish('/');

        foreach ($composer->namespaces as $namespace) {
            if ($relativePath->startsWith($namespace->path)) {
                return (string) $relativePath
                    ->replace($namespace->path, $namespace->namespace)
                    ->replace(['\\/', '//', '/'], '\\')
                    ->stripEnd('.php')
                    ->stripEnd('\\');
            }
        }

        throw new NoMatchingRegisteredNamespaceException($path);
    }

    /**
     * Gets the base class name from the specified file system path or FQCN.
     */
    function to_base_class_name(string $path): string
    {
        return str($path)
            ->replace(['/', '\\'], '/')
            ->stripEnd('/')
            ->stripEnd('.php')
            ->afterLast('/')
            ->classBasename()
            ->toString();
    }

    /**
     * This function is used internally by other namespace-related functions. It is not meant for userland usage.
     * @internal
     */
    function prepare_namespace(string $path, string $root = ''): ImmutableString
    {
        $normalized = str($path)
            ->stripStart($root)
            ->stripStart('/')
            ->replace(['/', '//'], '\\');

        // If the path is a to a PHP file, we exclude the file name. Otherwise,
        // it's a path to a directory, which should be included in the namespace.
        if ($normalized->endsWith('.php')) {
            return $normalized->beforeLast(['/', '\\']);
        }

        return $normalized;
    }
}
