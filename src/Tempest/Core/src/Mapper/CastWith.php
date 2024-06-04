<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Attribute;

#[Attribute]
final readonly class CastWith
{
    public function __construct(
        public string $className,
    ) {
    }
}
