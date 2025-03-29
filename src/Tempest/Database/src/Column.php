<?php

namespace Tempest\Database;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Column
{
    public function __construct(
        public string $name,
    ) {}
}
