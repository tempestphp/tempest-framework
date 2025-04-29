<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final class ContainerObjectRequiringC
{
    public function __construct(
        public ContainerObjectC $c,
    ) {}
}
