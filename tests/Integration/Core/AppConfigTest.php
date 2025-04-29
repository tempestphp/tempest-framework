<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use Tempest\Core\AppConfig;
use Tempest\Core\Environment;
use Tempest\Drift\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AppConfigTest extends FrameworkIntegrationTestCase
{
    public function test_defaults(): void
    {
        $appConfig = $this->container->get(AppConfig::class);

        $this->assertSame(Environment::TESTING, $appConfig->environment);
        $this->assertSame('', $appConfig->baseUri);
    }
}
