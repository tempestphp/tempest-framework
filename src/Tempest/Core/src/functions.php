<?php

declare(strict_types=1);

namespace Tempest {
    use Closure;
    use Tempest\Core\Composer;
    use Tempest\Core\DeferredTasks;
    use Tempest\Core\Kernel;

    use function Tempest\Support\path;
    use function Tempest\Support\str;

    /**
     * Creates a path scoped within the root of the project
     */
    function root_path(string ...$parts): string
    {
        return path(realpath(get(Kernel::class)->root), ...$parts)->toString();
    }

    /**
     * Creates an absolute path scoped within the framework's internal storage directory.
     */
    function internal_storage_path(string ...$parts): string
    {
        return path(get(Kernel::class)->internalStorage, ...$parts)->toString();
    }

    /**
     * Creates a relative path scoped within the main directory of the project.
     */
    function src_path(string ...$parts): string
    {
        $composer = get(Composer::class);

        return path($composer->mainNamespace->path, ...$parts)->toString();
    }

    /**
     * Creates a namespace scoped within the main namespace of the project.
     */
    function src_namespace(?string $append = null): string
    {
        $composer = get(Composer::class);

        return str($composer->mainNamespace->namespace)
            ->append($append ?? '')
            ->replace('\\\\', '\\')
            ->trim('\\')
            ->toString();
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
}
