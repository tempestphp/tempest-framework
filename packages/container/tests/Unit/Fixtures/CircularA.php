<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final readonly class CircularA
{
    public function __construct(
        public ContainerObjectA $other,
        public CircularB $b,
    ) {}
}
