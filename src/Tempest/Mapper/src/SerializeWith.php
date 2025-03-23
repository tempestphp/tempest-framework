<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Attribute;

#[Attribute]
final readonly class SerializeWith
{
    public function __construct(
        /** @var class-string<\Tempest\Mapper\Serializer> */
        public string $className,
    ) {}
}
