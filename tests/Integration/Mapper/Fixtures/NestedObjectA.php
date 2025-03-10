<?php

namespace Tests\Tempest\Integration\Mapper\Fixtures;

final class NestedObjectA
{
    public function __construct(
        /** @var \Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectB[] */
        public array $items,
    ) {}
}