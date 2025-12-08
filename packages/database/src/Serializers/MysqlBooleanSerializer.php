<?php

declare(strict_types=1);

namespace Tempest\Database\Serializers;

use Tempest\Mapper\Context;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

#[Context(Context::DATABASE_MYSQL)]
final class MysqlBooleanSerializer implements Serializer
{
    public static function for(): string
    {
        return 'bool';
    }

    public function serialize(mixed $input): string
    {
        if (! is_bool($input)) {
            throw new ValueCouldNotBeSerialized('boolean');
        }

        return $input ? '1' : '0';
    }
}
