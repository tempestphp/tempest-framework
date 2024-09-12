<?php

namespace Tests\Tempest\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use function Tempest\env;

class EnvHelperTest extends IntegrationTestCase
{
    public function test_env_fallback_value()
    {
        $this->assertTrue(env('missing', true));
    }

    #[DataProvider('types')]
    public function test_env_maps_values(string $value, mixed $expectedValue): void
    {
        putenv('test='.$value);

        $this->assertEquals($expectedValue, env('test'));
    }

    public static function types(): array
    {
        return [
            'true' => ['true', true],
            'false' => ['false', false],
            'null' => ['null', null],
            'empty' => ['', null],
        ];
    }
}
