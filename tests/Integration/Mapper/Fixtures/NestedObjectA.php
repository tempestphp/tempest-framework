<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\SerializeAs;

#[SerializeAs(self::class)]
final class NestedObjectA
{
    public function __construct(
        /** @var \Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectB[] */
        public array $items,
    ) {}
}
