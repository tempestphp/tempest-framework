<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\Serializers\HashedSerializer;
use Tempest\Mapper\ProvidesSerializer;

/**
 * The associated property will be hashed during serialization.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Hashed implements ProvidesSerializer
{
    public string $serializer {
        get => HashedSerializer::class;
    }
}
