<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

use Tests\Tempest\Container\Fixtures;

class ContainerObjectB
{
    public function __construct(public Fixtures\ContainerObjectA $a)
    {
    }
}
