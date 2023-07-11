<?php

namespace Tests\Tempest;

use Tempest\Http\RouteConfig;
use Tempest\Kernel;

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
