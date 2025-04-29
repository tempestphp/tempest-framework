<?php

namespace Tempest\Mapper\Tests\Integration\Fixtures;

final class NestedObjectA
{
    public function __construct(
        /** @var \Tempest\Mapper\Tests\Integration\Fixtures\NestedObjectB[] */
        public array $items,
    ) {}
}
