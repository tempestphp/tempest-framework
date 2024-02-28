<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

class ContainerObjectRequiringC
{
    public function __construct(public ContainerObjectC $c)
    {
    }
}
