<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class CircularC
{
    public function __construct(
        public ContainerObjectA $other,
        public CircularA $a
    ) {
    }
}
