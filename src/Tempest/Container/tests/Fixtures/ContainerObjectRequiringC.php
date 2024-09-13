<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

class ContainerObjectRequiringC
{
    public function __construct(public ContainerObjectC $c)
    {
    }
}
