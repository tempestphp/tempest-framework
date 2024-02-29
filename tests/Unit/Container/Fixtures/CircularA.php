<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class CircularA
{
    public function __construct(
        public ContainerObjectA $other,
        public CircularB $b,
    ) {
    }
}
