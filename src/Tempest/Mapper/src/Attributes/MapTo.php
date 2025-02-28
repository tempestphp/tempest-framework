<?php

declare(strict_types=1);

namespace Tempest\Mapper\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class MapTo {
    public function __construct(
        public readonly string $key
    ) {}
}
