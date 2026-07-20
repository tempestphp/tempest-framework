<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializers\StringSerializer;

final class StringSerializerTest extends TestCase
{
    #[Test]
    public function serialize(): void
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

    #[Test]
    public function only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new StringSerializer()->serialize([]);
    }
}
