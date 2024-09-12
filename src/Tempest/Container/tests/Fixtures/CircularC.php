<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class CircularC
{
    public function __construct(
        public ContainerObjectA $other,
        public CircularA $a
    ) {
    }
}
