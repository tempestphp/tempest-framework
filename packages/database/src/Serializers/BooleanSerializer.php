<?php

declare(strict_types=1);

namespace Tempest\Database\Serializers;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\DatabaseContext;
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

#[Context(DatabaseContext::class)]
final class BooleanSerializer implements Serializer
{
    public function __construct(
        private DatabaseContext $context,
    ) {}

    public static function for(): array
    {
        return ['bool', 'boolean'];
    }

    public function serialize(mixed $input): string
    {
        if (! is_bool($input)) {
            throw new ValueCouldNotBeSerialized('boolean');
        }

        return match ($this->context->dialect) {
            DatabaseDialect::POSTGRESQL => $input ? 'true' : 'false',
            default => $input ? '1' : '0',
        };
    }
}
