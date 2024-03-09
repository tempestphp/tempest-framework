<?php

declare(strict_types=1);

namespace Tempest;

use Tempest\Application\ConsoleKernel;
use Tempest\Application\HttpKernel;
use Tempest\Container\Container;

final readonly class Tempest
{
    public static function console(Container $container, string $basePath): ConsoleKernel
    {
        return new ConsoleKernel(
            container: $container,
            basePath: $basePath
        );
    }

    public static function http(Container $container, string $basePath): HttpKernel
    {
        return new HttpKernel(
            container: $container,
            basePath: $basePath
        );
    }
}
