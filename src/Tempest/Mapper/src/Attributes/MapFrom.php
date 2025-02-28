<?php

declare(strict_types=1);

namespace Tempest\Mapper\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class MapFrom
{
    public function __construct(
        public readonly string $key,
    ) {
    }
}
