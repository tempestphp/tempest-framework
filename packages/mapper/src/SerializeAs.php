<?php

namespace Tempest\Mapper;

use Attribute;

/**
 * Defines the name to use when serializing this class, instead of its fully qualified class name.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class SerializeAs
{
    public function __construct(
        public string $name,
    ) {}
}
