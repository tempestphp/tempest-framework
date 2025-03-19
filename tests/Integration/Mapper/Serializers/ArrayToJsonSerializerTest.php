<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializers\ArrayToJsonSerializer;

final class ArrayToJsonSerializerTest extends TestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            '{"foo":"bar"}',
            new ArrayToJsonSerializer()->serialize(['foo' => 'bar']),
        );
    }

    public function test_only_arrays_allowed(): void
    {
        $this->expectException(CannotSerializeValue::class);

        new ArrayToJsonSerializer()->serialize('foo');
    }
}