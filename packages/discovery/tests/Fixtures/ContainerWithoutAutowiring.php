<?php

namespace Tempest\Discovery\Tests\Fixtures;

use Psr\Container\ContainerInterface;

final class ContainerWithoutAutowiring implements ContainerInterface
{
    public function get(string $id): null
    {
        return null;
    }

    public function has(string $id): bool
    {
        return false;
    }
}
