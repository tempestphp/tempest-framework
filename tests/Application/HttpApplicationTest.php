<?php

namespace Tests\Tempest\Application;

use Tempest\Application\HttpApplication;
use Tests\Tempest\TestCase;

class HttpApplicationTest extends TestCase
{
    /** @test */
    public function test_run()
    {
        $app = $this->container->get(HttpApplication::class);

        $app->run();

        $this->assertTrue(true);
    }
}
