<?php

namespace Tempest\Core;

use Attribute;

#[Attribute]
final readonly class Experimental
{
    public function __construct(
        public string $name,
        public string $description,
    ) {}
}
