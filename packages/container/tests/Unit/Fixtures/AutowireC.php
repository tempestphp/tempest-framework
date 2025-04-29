<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Unit\Fixtures;

final readonly class AutowireC
{
    public function __construct(
        public ContainerObjectA $other,
        public string $unknown,
    ) {}
}
