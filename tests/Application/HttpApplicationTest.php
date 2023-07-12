<?php

namespace Tests\Tempest\Application;

use Tempest\Application\HttpApplication;
use Tests\Tempest\TestCase;

class HttpApplicationTest extends TestCase
{
    /** @test */
    public function test_run()
    {
        $app = new HttpApplication(__DIR__ . '/../../app');

        ob_start(fn () => null);
        $app->run();
        ob_end_clean();

        $this->assertTrue(true);
    }
}
