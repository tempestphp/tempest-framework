<?php

namespace Tests\Tempest\Integration\Mapper\Casters;

use Tempest\Mapper\Casters\DtoCaster;
use Tempest\Mapper\Exceptions\ValueCouldNotBeCast;
use Tempest\Mapper\MapperConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\MyObject;

final class DtoCasterTest extends FrameworkIntegrationTestCase
{
    public function test_cast(): void
    {
        $json = json_encode(['type' => MyObject::class, 'data' => ['name' => 'test']]);

        $dto = new DtoCaster(new MapperConfig())->cast($json);

        $this->assertInstanceOf(MyObject::class, $dto);
        $this->assertSame('test', $dto->name);
    }

    public function test_cast_with_map(): void
    {
        $config = new MapperConfig()->serializeAs(MyObject::class, 'my-object');

        $json = json_encode(['type' => 'my-object', 'data' => ['name' => 'test']]);

        $dto = new DtoCaster($config)->cast($json);

        $this->assertInstanceOf(MyObject::class, $dto);
        $this->assertSame('test', $dto->name);
    }

    public function test_cannot_cast_with_invalid_json(): void
    {
        $json = '';

        $this->expectException(ValueCouldNotBeCast::class);

        new DtoCaster(new MapperConfig())->cast($json);
    }
}
