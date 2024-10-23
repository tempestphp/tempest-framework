<?php

declare(strict_types=1);

namespace Tempest {

    use Tempest\Core\Kernel;

    function root_path(string ...$parts): string
    {
        return path(realpath(get(Kernel::class)->root), ...$parts);
    }

    function path(string ...$parts): string
    {
        $path = implode('/', $parts);

        return str_replace(
            ['///', '//', '\\', '\\\\'],
            '/',
            $path,
        );
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
}
