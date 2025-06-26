<?php

namespace Tests\Tempest\Integration\Route;

use Tempest\Router\RouteConfig;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Route\Fixtures\ConvertsToResponseExceptionController;
use Tests\Tempest\Integration\Route\Fixtures\CustomNotFoundMiddleware;

/**
 * @property \Tempest\Framework\Testing\Http\HttpRouterTester $http
 */
final class HandleRouteExceptionMiddlewareTest extends FrameworkIntegrationTestCase
{
    public function test_unmatched_route_returns_not_found(): void
    {
        $this->http->get('unknown-route')->assertNotFound();
    }

    public function test_custom_not_found_middleware(): void
    {
        $routeConfig = $this->container->get(RouteConfig::class);
        $routeConfig->middleware->add(CustomNotFoundMiddleware::class);

        $this->http->get('unknown-route')->assertHasHeader('x-not-found');
    }

    public function test_exception_implementing_converts_to_response(): void
    {
        $this->registerRoute(ConvertsToResponseExceptionController::class);

        $this->http
            ->get('/converts-to-response-exception')
            ->assertOk()
            ->assertSee('Test');
    }
}
