<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Http\RouteConfig;

class KernelTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function test_discovery()
    {
        $kernel = new Kernel(new AppConfig(
            appPath: __DIR__ . '/../app/',
            appNamespace: 'App\\',
        ));

        $container = $kernel->init();

        $config = $container->get(RouteConfig::class);

        $this->assertTrue(count($config->routes) > 1);
    }
}
