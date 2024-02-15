<?php

declare(strict_types=1);

namespace Tests\Tempest;

use Tempest\Application\Kernel;
use Tempest\Http\RouteConfig;

class KernelTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function test_discovery()
    {
        $kernel = new Kernel();

        $container = $kernel->init(__DIR__ . '/../app/', 'App\\');

        $config = $container->get(RouteConfig::class);

        $this->assertTrue(count($config->routes) > 1);
    }
}
