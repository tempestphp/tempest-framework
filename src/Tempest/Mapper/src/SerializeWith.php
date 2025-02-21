<?php

namespace Tempest\Mapper;

use Attribute;

#[Attribute]
final class SerializeWith
{
    public function __construct(
        /** @var class-string<\Tempest\Mapper\Serializer> */
        public string $className,
    ) {
    }
}