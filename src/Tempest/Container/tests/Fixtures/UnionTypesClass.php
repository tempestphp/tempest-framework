<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

class UnionTypesClass
{
    public function __construct(
        public ContainerObjectC|ContainerObjectA $input
    ) {
    }
}
