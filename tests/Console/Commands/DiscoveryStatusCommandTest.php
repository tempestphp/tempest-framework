<?php

declare(strict_types=1);

use Tempest\AppConfig;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('discovery status command', function () {
    $output = $this->console('discovery:status')->asText();

    $appConfig = $this->container->get(AppConfig::class);

    foreach ($appConfig->discoveryClasses as $discoveryClass) {
        $this->assertStringContainsString($discoveryClass, $output);
    }

    foreach ($appConfig->discoveryLocations as $discoveryLocation) {
        $this->assertStringContainsString($discoveryLocation->path, $output);
    }
});
