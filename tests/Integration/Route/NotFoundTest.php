<?php

namespace Tests\Tempest\Integration\Route;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class NotFoundTest extends FrameworkIntegrationTestCase
{
    public function test_unmatched_route_returns_not_found(): void
    {
        $this->http->get('unknown-route')->assertNotFound();
    }
}