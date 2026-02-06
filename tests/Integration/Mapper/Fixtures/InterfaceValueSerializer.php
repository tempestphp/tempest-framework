<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Fixtures;

use Tempest\Mapper\Serializer;

final class InterfaceValueSerializer implements Serializer
{
    public static function for(): string
    {
        return InterfaceWithSerializeWith::class;
    }

    public function serialize(mixed $input): string
    {
        return 'serialized:' . $input->getValue();
    }
}
