<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class QueryBuilder
{
    public function __construct(public string $builderClass)
    {
    }
}
