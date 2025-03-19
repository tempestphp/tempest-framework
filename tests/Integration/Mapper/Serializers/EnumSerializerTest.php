<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\DateTimeSerializer;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tests\Tempest\Integration\Mapper\Fixtures\BackedToSerialize;
use Tests\Tempest\Integration\Mapper\Fixtures\UnitEnumToSerializer;

final class EnumSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            'foo',
            new EnumSerializer()->serialize(BackedToSerialize::FOO),
        );

        $this->assertSame(
            'FOO',
            new EnumSerializer()->serialize(UnitEnumToSerializer::FOO),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new EnumSerializer()->serialize('foo');
    }
}