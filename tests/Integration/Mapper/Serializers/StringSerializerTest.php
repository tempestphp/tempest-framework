<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\StringSerializer;

final class StringSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            'a',
            new StringSerializer()->serialize('a'),
        );

        $this->assertSame(
            'a',
            new StringSerializer()->serialize(\Tempest\Support\str('a')),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new StringSerializer()->serialize('foo');
    }
}