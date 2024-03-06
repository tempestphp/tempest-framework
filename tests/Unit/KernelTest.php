<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit;

use PHPUnit\Framework\TestCase;
use Tempest\AppConfig;
use Tempest\Application\OldKernel;
use Tempest\Http\RouteConfig;

/**
 * @internal
 * @small
 */
class KernelTest extends TestCase
{
    public function test_discovery()
    {
        $kernel = new OldKernel(__DIR__ . '/../../', new AppConfig(__DIR__ . '/../../'));

        $container = $kernel->init();

        $config = $container->get(RouteConfig::class);

        $this->assertTrue(count($config->routes) > 1);
    }
}
