<?php

declare(strict_types=1);

namespace Tempest {
    use Closure;
    use Stringable;
    use Tempest\Core\Composer;
    use Tempest\Core\DeferredTasks;
    use Tempest\Core\ExceptionReporter;
    use Tempest\Core\Kernel;
    use Tempest\Support\Namespace\PathCouldNotBeMappedToNamespaceException;
    use Throwable;

    use function Tempest\Support\Namespace\to_psr4_namespace;
    use function Tempest\Support\Path\to_absolute_path;

    /**
     * Creates an absolute path scoped to the root of the project.
     */
    function root_path(Stringable|string ...$parts): string
    {
        return to_absolute_path(get(Kernel::class)->root, ...$parts);
    }

    /**
     * Creates an absolute path scoped to the main directory of the project.
     */
    function src_path(Stringable|string ...$parts): string
    {
        return root_path(get(Composer::class)->mainNamespace->path, ...$parts);
    }

    /**
     * Creates an absolute path scoped to the framework's internal storage directory.
     */
    function internal_storage_path(Stringable|string ...$parts): string
    {
        return to_absolute_path(get(Kernel::class)->internalStorage, ...$parts);
    }

    /**
     * Converts the given path to a registered namespace. The path is expected to be absolute, or relative to the root of the project.
     *
     * @throws PathCouldNotBeMappedToNamespaceException If the path cannot be mapped to registered namespace
     */
    function registered_namespace(Stringable|string ...$parts): string
    {
        return to_psr4_namespace(get(Composer::class)->namespaces, root_path(...$parts), root: root_path());
    }

    /**
     * Converts the given path to the main namespace. The path is expected to be absolute, or relative to the root of the project.
     *
     * @throws PathCouldNotBeMappedToNamespaceException If the path cannot be mapped to the main namespace
     */
    function src_namespace(Stringable|string ...$parts): string
    {
        return to_psr4_namespace(get(Composer::class)->mainNamespace, root_path(...$parts), root: root_path());
    }

    /**
     * Retrieves the given `$key` from the environment variables. If `$key` is not defined, `$default` is returned instead.
     */
    function env(string $key, mixed $default = null): mixed
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        return match (strtolower($value)) {
            'true' => true,
            'false' => false,
            'null', '' => null,
            default => $value,
        };
    }

    /**
     * Defer a task, will be run after a request has been sent or a command has executed
     */
    function defer(Closure $closure): void
    {
        get(DeferredTasks::class)->add($closure);
    }

    /**
     * Passes the given exception through registered exception processors.
     */
    function report(Throwable $throwable): void
    {
        get(ExceptionReporter::class)->report($throwable);
    }
}
