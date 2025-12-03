<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;

interface Kernel
{
    public const string VERSION = '2.12.0';

    public string $root {
        get;
    }

    public string $internalStorage {
        get;
    }

    public array $discoveryLocations {
        get;
        set;
    }

    public array $discoveryClasses {
        get;
        set;
    }

    public Container $container {
        get;
    }

    public static function boot(
        string $root,
        array $discoveryLocations = [],
        ?Container $container = null,
        ?string $internalStorage = null,
    ): self;

    public function shutdown(int|string $status = ''): never;
}
