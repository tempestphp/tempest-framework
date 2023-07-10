<?php

namespace Tempest\Container;

use Attribute;

#[Attribute]
final readonly class InitializedBy
{
    public function __construct(
        public string $className,
    ) {}
}