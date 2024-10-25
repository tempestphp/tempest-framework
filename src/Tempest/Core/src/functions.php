<?php

declare(strict_types=1);

namespace Tempest {

    use Closure;
    use Tempest\Core\DeferredTasks;
    use Tempest\Core\Kernel;

    function path(string ...$parts): string
    {
        $path = implode('/', $parts);

        return str_replace(
            ['///', '//', '\\', '\\\\'],
            '/',
            $path,
        );
    }

    function root_path(string ...$parts): string
    {
        return path(realpath(get(Kernel::class)->root), ...$parts);
    }

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

    function defer(Closure $closure): void
    {
        get(DeferredTasks::class)->add($closure);
    }
}
