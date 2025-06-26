<?php

namespace Tests\Tempest\Integration\Mapper\Casters;

use Tempest\Mapper\Casters\DtoCaster;
use Tempest\Mapper\Exceptions\ValueCouldNotBeCast;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\MyObject;

final class DtoCasterTest extends FrameworkIntegrationTestCase
{
    public function test_cast(): void
    {
        $json = json_encode(['type' => MyObject::class, 'data' => ['name' => 'test']]);

        $dto = new DtoCaster()->cast($json);

        $this->assertInstanceOf(MyObject::class, $dto);
        $this->assertSame('test', $dto->name);
    }

    public function test_cannot_cast_with_invalid_json(): void
    {
        $json = '';

        $this->expectException(ValueCouldNotBeCast::class);

        new DtoCaster()->cast($json);
    }
}
