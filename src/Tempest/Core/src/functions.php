<?php

declare(strict_types=1);

namespace Tempest {

    use Closure;
    use Tempest\Core\Composer;
    use Tempest\Core\DeferredTasks;
    use Tempest\Core\Kernel;
    use function Tempest\Support\str;

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

    function src_path(string ...$parts): string
    {
        $composer = get(Composer::class);

        return path($composer->mainNamespace->path, ...$parts);
    }

    function src_namespace(?string $append = null): string
    {
        $composer = get(Composer::class);

        return str($composer->mainNamespace->namespace)
            ->append($append ?? '')
            ->replace('\\\\', '\\')
            ->trim('\\')
            ->toString();
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
