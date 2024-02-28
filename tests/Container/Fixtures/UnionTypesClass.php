<?php

declare(strict_types=1);

namespace Tests\Tempest\Container\Fixtures;

class UnionTypesClass
{
    public function __construct(
        public ContainerObjectC|ContainerObjectA $input
    ) {
    }
}
