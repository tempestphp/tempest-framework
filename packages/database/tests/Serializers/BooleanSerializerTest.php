<?php

namespace Tempest\Database\Tests\Serializers;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\DatabaseContext;
use Tempest\Database\Serializers\BooleanSerializer;

final class BooleanSerializerTest extends TestCase
{
    #[Test]
    #[TestWith([DatabaseDialect::SQLITE, true, '1'])]
    #[TestWith([DatabaseDialect::SQLITE, false, '0'])]
    #[TestWith([DatabaseDialect::MYSQL, true, '1'])]
    #[TestWith([DatabaseDialect::MYSQL, false, '0'])]
    #[TestWith([DatabaseDialect::POSTGRESQL, false, 'false'])]
    #[TestWith([DatabaseDialect::POSTGRESQL, true, 'true'])]
    public function can_serialize(DatabaseDialect $dialect, bool $input, mixed $expected): void
    {
        $serializer = new BooleanSerializer(new DatabaseContext($dialect));

        $this->assertSame($expected, $serializer->serialize($input));
    }
}
