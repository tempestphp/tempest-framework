<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Core;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Core\AppConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AppConfigTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function defaults(): void
    {
        $appConfig = $this->container->get(AppConfig::class);

        $this->assertSame('', $appConfig->baseUri);
        $this->assertSame(null, $appConfig->name);
    }
}
