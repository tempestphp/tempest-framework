<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final class ContainerObjectC
{
    public function __construct(
        public string $prop,
    ) {}
}
