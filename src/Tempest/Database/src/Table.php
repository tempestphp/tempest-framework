<?php

namespace Tempest\Database;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Table
{
    public function __construct(
        public ?string $name = null,
    ) {}
}
