<?php

declare(strict_types=1);

namespace Tempest\Support\Namespace {
    use Stringable;
    use Tempest\Support\Str\ImmutableString;

    use function Tempest\Support\Arr\wrap;
    use function Tempest\Support\Path\to_relative_path;
    use function Tempest\Support\str;
    use function Tempest\Support\Str\ensure_ends_with;
    use function Tempest\Support\Str\to_pascal_case;

    /**
     * Converts the given file system path to the equivalent PSR-4 namespace. The `$root` is stripped from the namespace.
     *
     * ## Example
     * ```php
     * to_namespace('app/Auth/User.php'); // App\Auth
     * to_namespace('app/Auth/User'); // App\Auth\User
     * ```
     */
    function to_namespace(Stringable|string $path, null|Stringable|string $root = null): string
    {
        return prepare_namespace($path, $root)
            ->stripEnd('\\')
            ->explode('\\')
            ->map(fn (string $segment) => to_pascal_case($segment))
            ->implode('\\')
            ->toString();
    }

    /**
     * Converts the given file system path to the equivalent specified PSR-4 namespace.
     * The given path is expected to be absolute or relative to the root path. An exception will be thrown otherwise.
     *
     * @param array<Psr4Namespace> $namespaces
     *
     * ## Example
     * ```php
     * // Main namespace is `App`, with path `app/`.
     * to_psr4_namespace(new Psr4Namespace('App', 'app/'), 'app/Auth/User.php'); // App\Auth
     * to_psr4_namespace(new Psr4Namespace('App', 'app/'), 'app/Auth/User'); // App\Auth\User
     * ```
     */
    function to_psr4_namespace(Psr4Namespace|array $namespaces, Stringable|string $path, null|Stringable|string $root = null): string
    {
        $relativePath = prepare_namespace($path, $root)
            ->stripEnd('\\')
            ->replace('\\', '/')
            ->finish('/');

        foreach (wrap($namespaces) as $namespace) {
            $namespacePath = ltrim(to_relative_path($root, $namespace->path), './');

            if ($relativePath->startsWith($namespacePath)) {
                return (string) $relativePath
                    ->replaceStart($namespacePath, $namespace->namespace)
                    ->replace(['\\/', '//', '/'], '\\')
                    ->stripEnd('.php')
                    ->stripEnd('\\');
            }
        }

        throw new PathCouldNotBeMappedToNamespace($path);
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
    function prepare_namespace(Stringable|string $path, null|Stringable|string $root = null): ImmutableString
    {
        $normalized = str($path)
            ->stripStart($root ?? '')
            ->stripStart('/')
            ->replace(['/', '//'], '\\');

        // If the path is a to a PHP file, we exclude the file name. Otherwise,
        // it's a path to a directory, which should be included in the namespace.
        if ($normalized->endsWith('.php')) {
            return $normalized->contains(['/', '\\'])
                ? $normalized->beforeLast(['/', '\\'])
                : new ImmutableString();
        }

        return $normalized;
    }
}
