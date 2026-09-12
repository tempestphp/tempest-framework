<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Router;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Router\MatchedRoute;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class RouterResetTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function reset_clears_the_matched_route(): void
    {
        $this->http->get('/repeated/a')->assertOk();

        $this->assertTrue($this->container->has(MatchedRoute::class));

        $this->container->reset();

        $this->assertFalse($this->container->has(MatchedRoute::class));
    }
}
