<?php

declare(strict_types=1);

namespace Tempest\Core\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use function Tempest\env;
use Tests\Tempest\Integration\IntegrationTestCase;

/**
 * @internal
 * @small
 */
final class EnvHelperTest extends IntegrationTestCase
{
    public function test_env_fallback_value(): void
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
