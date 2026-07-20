<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tests\Tempest\Integration\Mapper\Fixtures\BackedEnumToSerialize;
use Tests\Tempest\Integration\Mapper\Fixtures\UnitEnumToSerialize;

final class EnumSerializerTest extends TestCase
{
    #[Test]
    public function serialize(): void
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

    #[Test]
    public function only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new EnumSerializer()->serialize('foo');
    }
}
