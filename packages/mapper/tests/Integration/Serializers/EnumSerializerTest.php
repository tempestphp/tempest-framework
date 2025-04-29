<?php

namespace Tempest\Mapper\Tests\Integration\Serializers;

use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tempest\Mapper\Tests\Integration\Fixtures\BackedEnumToSerialize;
use Tempest\Mapper\Tests\Integration\Fixtures\UnitEnumToSerialize;

final class EnumSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            'foo',
            new EnumSerializer()->serialize(BackedEnumToSerialize::FOO),
        );

        $this->assertSame(
            'FOO',
            new EnumSerializer()->serialize(UnitEnumToSerialize::FOO),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new EnumSerializer()->serialize('foo');
    }
}
