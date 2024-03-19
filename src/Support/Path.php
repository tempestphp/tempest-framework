<?php

declare(strict_types=1);

namespace Tempest\Support;

use const DIRECTORY_SEPARATOR;

final class Path
{
    public static function canonicalize(string $path): string
    {
        return realpath($path);
    }

    public static function normalize(string $path, string $separator = DIRECTORY_SEPARATOR): string
    {
        return str_replace(['/', '\\'], $separator, $path);
    }

    public static function join(string ...$parts): string
    {
        $path = implode(DIRECTORY_SEPARATOR, $parts);

        return self::normalize($path);
    }

    public static function joinWith(string $separator, string ...$parts): string
    {
        $path = implode($separator, $parts);

        return self::normalize($path, $separator);
    }
}
