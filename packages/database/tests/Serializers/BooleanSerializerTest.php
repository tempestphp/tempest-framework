<?php

namespace Tempest\Database\Tests\Serializers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Serializers\MysqlBooleanSerializer;
use Tempest\Database\Serializers\PostgresBooleanSerializer;
use Tempest\Database\Serializers\SqliteBooleanSerializer;

final class BooleanSerializerTest extends TestCase
{
    #[Test]
    #[TestWith([SqliteBooleanSerializer::class, true, '1'])]
    #[TestWith([SqliteBooleanSerializer::class, false, '0'])]
    #[TestWith([MysqlBooleanSerializer::class, true, '1'])]
    #[TestWith([MysqlBooleanSerializer::class, false, '0'])]
    #[TestWith([PostgresBooleanSerializer::class, false, 'false'])]
    #[TestWith([PostgresBooleanSerializer::class, true, 'true'])]
    public function can_serialize(string $class, bool $input, mixed $expected): void
    {
        $serializer = new $class();
        $this->assertSame($expected, $serializer->serialize($input));
    }
}
