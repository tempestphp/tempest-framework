<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Router;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Router\MatchedRoute;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class MatchRouteMiddlewareTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function unmatched_request_does_not_leave_the_previous_matched_route_bound(): void
    {
        $this->http->get('/repeated/a')->assertOk();

        $this->assertTrue($this->container->has(MatchedRoute::class));

        $this->http->get('/does-not-exist')->assertNotFound();

        $this->assertFalse($this->container->has(MatchedRoute::class));
    }
}
