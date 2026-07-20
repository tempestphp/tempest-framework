<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\ArrayToJsonSerializer;
use Tempest\Support\Arr\ImmutableArray;

final class ArrayToJsonSerializerTest extends TestCase
{
    #[Test]
    public function serialize(): void
    {
        $this->assertSame(
            '{"foo":"bar"}',
            new ArrayToJsonSerializer()->serialize(['foo' => 'bar']),
        );

        $this->assertSame(
            '{"foo":"bar"}',
            new ArrayToJsonSerializer()->serialize(new ImmutableArray(['foo' => 'bar'])),
        );
    }

    #[Test]
    public function only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new ArrayToJsonSerializer()->serialize('foo');
    }
}
