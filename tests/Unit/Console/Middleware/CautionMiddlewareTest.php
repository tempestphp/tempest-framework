<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Middleware;

use Tempest\AppConfig;
use Tempest\Environment;
use Tests\Tempest\Unit\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
class CautionMiddlewareTest extends ConsoleIntegrationTestCase
{
    public function test_in_local(): void
    {
        $this->console
            ->call('cautioncommand')
            ->assertContains('CAUTION confirmed');
    }

    public function test_in_production(): void
    {
        $appConfig = $this->container->get(AppConfig::class);
        $appConfig->environment = Environment::PRODUCTION;

        $this->console
            ->call('cautioncommand')
            ->submit('yes')
            ->assertContains('CAUTION confirmed');
    }
}
