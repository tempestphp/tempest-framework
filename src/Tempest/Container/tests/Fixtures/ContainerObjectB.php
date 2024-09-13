<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

class ContainerObjectB
{
    public function __construct(public ContainerObjectA $a)
    {
    }
}
