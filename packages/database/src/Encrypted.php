<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;
use Tempest\Database\Casters\EncryptedCaster;
use Tempest\Database\Serializers\EncryptedSerializer;
use Tempest\Database\Serializers\HashedSerializer;
use Tempest\Mapper\ProvidesCaster;
use Tempest\Mapper\ProvidesSerializer;

/**
 * The associated property will be encrypted during serialization and decrypted during casting.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Encrypted implements ProvidesSerializer, ProvidesCaster
{
    public string $serializer {
        get => EncryptedSerializer::class;
    }

    public string $caster {
        get => EncryptedCaster::class;
    }
}
