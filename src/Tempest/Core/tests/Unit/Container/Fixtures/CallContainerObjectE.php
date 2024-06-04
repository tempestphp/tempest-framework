<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

class CallContainerObjectE
{
    public function method(ContainerObjectE $input): ContainerObjectE
    {
        return $input;
    }
}
