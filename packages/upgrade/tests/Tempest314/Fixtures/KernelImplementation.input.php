<?php

namespace Tempest\Upgrade\Tests\Tempest314\Fixtures;

use Tempest\Container\Container;
use Tempest\Core\Kernel;

final class CustomKernel implements Kernel
{
    public string $root;

    public string $internalStorage;

    public Container $container;

    public static function boot(
        string $root,
        array $discoveryLocations = [],
        ?Container $container = null,
        ?string $internalStorage = null,
    ): self {
        return new self();
    }

    public function shutdown(int|string $status = ''): self
    {
        return $this;
    }
}
