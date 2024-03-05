<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit;

use PHPUnit\Framework\TestCase;
use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Http\RouteConfig;

/**
 * @internal
 * @small
 */
class KernelTest extends TestCase
{
    public function test_discovery()
    {
        $kernel = new Kernel(__DIR__ . '/../../', new AppConfig());

        $container = $kernel->init();

        $config = $container->get(RouteConfig::class);

        $this->assertTrue(count($config->routes) > 1);
    }
}
