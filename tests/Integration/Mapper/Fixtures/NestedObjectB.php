<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\SerializeAs;

#[SerializeAs(self::class)]
final class NestedObjectB
{
    public function __construct(
        public string $name,
    ) {}
}
