<?php

namespace Tests\Tempest\Integration\Http;

use Tempest\Router\RouteConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Http\Fixtures\CustomErrorMiddleware;

final class HttpMiddlewarePriorityTest extends FrameworkIntegrationTestCase
{
    public function test_hook_into_error_handler(): void
    {
        /** @var \Tempest\Router\RouteConfig $routeConfig */
        $routeConfig = $this->get(RouteConfig::class);

        $routeConfig->middleware->add(CustomErrorMiddleware::class);

        $this->http
            ->get('/unknown-route-for-middleware-test')
            ->assertOk()
            ->assertSee('Something went wrong!');
    }
}