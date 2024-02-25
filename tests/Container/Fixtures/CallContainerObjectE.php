<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

use Tests\Tempest\Container\Fixtures;

class CallContainerObjectE
{
    public function method(Fixtures\ContainerObjectE $input): Fixtures\ContainerObjectE
    {
        return $input;
    }
}
