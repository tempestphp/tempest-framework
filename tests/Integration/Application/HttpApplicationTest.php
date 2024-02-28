<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tempest\AppConfig;
use Tempest\Application\HttpApplication;
use Tests\Tempest\Integration\TestCase;

class HttpApplicationTest extends TestCase
{
    /** @test */
    public function test_http_application_run()
    {
        $app = new HttpApplication(
            $this->container,
            $this->container->get(AppConfig::class),
        );

        ob_start();
        $app->run();
        $contents = ob_get_clean();

        $this->assertStringContainsString('<html', $contents);
    }
}
