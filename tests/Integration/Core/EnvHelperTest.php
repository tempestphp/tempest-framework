<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\env;

/**
 * @internal
 */
final class EnvHelperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function env_fallback_value(): void
    {
        $this->assertTrue(env('missing', true));
    }

    #[DataProvider('types')]
    #[Test]
    public function env_maps_values(string $value, mixed $expectedValue): void
    {
        putenv('test=' . $value);

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
