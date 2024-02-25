<?php

declare(strict_types=1);

use Tempest\AppConfig;
use Tempest\Application\HttpApplication;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('http application run', function () {
    $app = new HttpApplication(
        $this->container,
        $this->container->get(AppConfig::class),
    );

    ob_start();
    $app->run();
    $contents = ob_get_clean();

    $this->assertStringContainsString('<html', $contents);
});
