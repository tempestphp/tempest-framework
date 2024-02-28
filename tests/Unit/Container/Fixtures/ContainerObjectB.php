<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

class ContainerObjectB
{
    public function __construct(public \Tests\Tempest\Unit\Container\Fixtures\ContainerObjectA $a)
    {
    }
}
