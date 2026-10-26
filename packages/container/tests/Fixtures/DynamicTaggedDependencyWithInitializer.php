<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class DynamicTaggedDependencyWithInitializer
{
    public function __construct(
        public ?string $name = null,
    ) {}
}
