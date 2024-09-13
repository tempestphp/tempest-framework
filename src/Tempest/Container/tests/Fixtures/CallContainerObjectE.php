<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

class CallContainerObjectE
{
    public function method(ContainerObjectE $input): ContainerObjectE
    {
        return $input;
    }
}
