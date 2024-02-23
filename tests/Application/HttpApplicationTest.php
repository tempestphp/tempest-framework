<?php

namespace Tests\Tempest\Application;

use Tempest\Application\HttpApplication;
use Tests\Tempest\TestCase;

class HttpApplicationTest extends TestCase
{
    /** @test */
    public function test_http_application_run()
    {
        $app = new HttpApplication($this->container);

        ob_start();
        $app->run();
        $contents = ob_get_clean();

        $this->assertStringContainsString('<html', $contents);
    }
}
