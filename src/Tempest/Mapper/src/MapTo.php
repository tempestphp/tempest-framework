<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class MapTo
{
    public const string JSON = 'json';

    public const string ARRAY = 'array';

    public function __construct(
        public string $name,
    ) {
    }
}
