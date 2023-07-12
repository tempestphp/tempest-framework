<?php

namespace Tests\Tempest;

use Tempest\Application\Kernel;
use Tempest\Http\RouteConfig;

class KernelTest extends \PHPUnit\Framework\TestCase
{
    /** @test */
    public function test_discovery()
    {
        $kernel = new Kernel();

        $container = $kernel->init(__DIR__ . '/../app/');

        $config = $container->get(RouteConfig::class);

        $this->assertTrue(count($config->controllers) > 1);
    }
}
