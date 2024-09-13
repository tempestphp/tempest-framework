<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class AutowireC
{
    public function __construct(
        public ContainerObjectA $other,
        public string $unknown
    ) {
    }
}
