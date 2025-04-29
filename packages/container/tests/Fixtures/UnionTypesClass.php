<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final class UnionTypesClass
{
    public function __construct(
        public ContainerObjectC|ContainerObjectA $input,
    ) {}
}
