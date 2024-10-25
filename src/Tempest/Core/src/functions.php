<?php

declare(strict_types=1);

namespace Tempest {

    use Closure;
    use Tempest\Core\DeferredTasks;

    /**
     * Creates and sanitizes a file system path from the given `$parts`. The resulting path is not checked against the file system.
     */
    function path(string ...$parts): string
    {
        $path = implode('/', $parts);

        return str_replace(
            ['///', '//', '\\', '\\\\'],
            '/',
            $path,
        );
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
