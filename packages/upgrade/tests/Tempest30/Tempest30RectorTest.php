<?php

namespace Tempest\Upgrade\Tests\Tempest30;

use PHPUnit\Framework\TestCase;
use Tempest\Upgrade\Tests\RectorTester;

final class Tempest30RectorTest extends TestCase
{
    private RectorTester $rector {
        get => new RectorTester(__DIR__ . '/tempest30_rector.php');
    }

    public function test_writeable_routes(): void
    {
        $this->rector
            ->runFixture(__DIR__ . '/Fixtures/CustomRoute.input.php')
            ->assertContains('final class CustomRoute implements Route');
    }
}
