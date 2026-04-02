<?php

namespace Tempest\Generation\Tests\TypeScript\Fixtures\Metadata;

use Attribute;

#[Attribute]
final class Description implements EnumMetadata
{
    public string $key {
        get => 'description';
    }

    public function __construct(
        public readonly string $value,
    ) {}
}
