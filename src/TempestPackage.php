<?php

declare(strict_types=1);

namespace Tempest;

use Tempest\Discovery\CommandBusDiscovery;
use Tempest\Discovery\ConsoleCommandDiscovery;
use Tempest\Discovery\MigrationDiscovery;
use Tempest\Discovery\RouteDiscovery;
use Tempest\Interface\Package;

final readonly class TempestPackage implements Package
{
    public function getPath(): string
    {
        return __DIR__;
    }

    public function getNamespace(): string
    {
        return 'Tempest\\';
    }

    public function getDiscovery(): array
    {
        return  [
            RouteDiscovery::class,
            MigrationDiscovery::class,
            ConsoleCommandDiscovery::class,
            CommandBusDiscovery::class,
        ];
    }
}
