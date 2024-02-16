<?php

declare(strict_types=1);

namespace Tests\Tempest;

use App\AppPackage;
use Tempest\AppConfig;
use Tempest\Application\Kernel;
use Tempest\Http\RouteConfig;
use Tempest\TempestPackage;

class KernelTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function test_discovery()
    {
        $kernel = new Kernel(new AppConfig(
            packages: [
                new TempestPackage(),
                new AppPackage(),
            ],
        ));

        $container = $kernel->init();

        $config = $container->get(RouteConfig::class);

        $this->assertTrue(count($config->routes) > 1);
    }
}
