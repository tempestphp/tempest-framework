<?php

declare(strict_types=1);

namespace Tempest\Support;

final readonly class PathHelper
{
    public static function make(string ...$parts): string
    {
        $path = implode('/', $parts);

        return str_replace(
            ['//', '\\', '\\\\'],
            ['/', '/', '/'],
            $path,
        );
    }
}
