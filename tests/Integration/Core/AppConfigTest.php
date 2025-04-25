<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Core\AppConfig;
use Tempest\Core\Environment;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class AppConfigTest extends FrameworkIntegrationTestCase
{
    public function test_defaults(): void
    {
        $appConfig = $this->container->get(AppConfig::class);

        $this->assertSame(Environment::TESTING, $appConfig->environment);
        $this->assertSame('', $appConfig->baseUri);
    }
}
