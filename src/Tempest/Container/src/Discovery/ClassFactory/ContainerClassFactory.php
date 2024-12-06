<?php

namespace Tempest\Container\Discovery\ClassFactory;

use Tempest\Container\Container;

final readonly class ContainerClassFactory implements ClassFactory
{
    public function __construct(private Container $container)
    {
    }

    public function create(string $class): object
    {
        return $this->container->get($class);
    }
}