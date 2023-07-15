<?php

namespace Tempest\ORM\Attributes;

use Attribute;

#[Attribute]
final readonly class CastWith
{
    public function __construct(
        public string $className,
    ) {}
}