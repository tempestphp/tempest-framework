<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;

interface Kernel
{
    public const string VERSION = '3.16.0';

    public string $root { get; }

    public string $internalStorage { get; }

    public Container $container { get; }

    public static function boot(
        string $root,
        array $discoveryLocations = [],
        ?Container $container = null,
        ?string $internalStorage = null,
    ): self;

    public function shutdown(int|string $status = ''): void;
}
