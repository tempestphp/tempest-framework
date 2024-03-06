<?php

declare(strict_types=1);

namespace Tempest;

use Tempest\Application\ConsoleKernel;
use Tempest\Application\HttpKernel;
use Tempest\Container\Container;

final readonly class Tempest
{
    public static function console(Container $container, string $rootDirectory): ConsoleKernel
    {
        return new ConsoleKernel(
            container: $container,
            rootDirectory: $rootDirectory
        );
    }

    public static function http(Container $container, string $rootDirectory): HttpKernel
    {
        return new HttpKernel(
            container: $container,
            rootDirectory: $rootDirectory
        );
    }
}
