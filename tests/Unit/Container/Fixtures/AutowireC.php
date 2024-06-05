<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class AutowireC
{
    public function __construct(
        public ContainerObjectA $other,
        public string $unknown
    ) {
    }
}
