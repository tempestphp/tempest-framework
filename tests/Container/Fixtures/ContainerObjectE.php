<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

class ContainerObjectE
{
    public function __construct(public string $id = 'default')
    {
    }
}
