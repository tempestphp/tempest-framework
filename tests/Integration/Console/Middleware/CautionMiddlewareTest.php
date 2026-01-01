<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Core\Environment;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CautionMiddlewareTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function in_local(): void
    {
        $this->container->singleton(Environment::class, Environment::LOCAL);

        $this->console
            ->call('caution')
            ->assertContains('CAUTION confirmed');
    }

    #[Test]
    #[TestWith([Environment::PRODUCTION])]
    #[TestWith([Environment::STAGING])]
    public function in_caution_environments(Environment $environment): void
    {
        $this->container->singleton(Environment::class, $environment);

        $this->console
            ->call('caution')
            ->submit('yes')
            ->assertContains('CAUTION confirmed');
    }
}
