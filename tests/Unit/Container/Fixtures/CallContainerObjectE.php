<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

class CallContainerObjectE
{
    public function method(\Tests\Tempest\Unit\Container\Fixtures\ContainerObjectE $input): \Tests\Tempest\Unit\Container\Fixtures\ContainerObjectE
    {
        return $input;
    }
}
