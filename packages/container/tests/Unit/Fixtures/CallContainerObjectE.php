<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final class CallContainerObjectE
{
    public function method(ContainerObjectE $input): ContainerObjectE
    {
        return $input;
    }
}
