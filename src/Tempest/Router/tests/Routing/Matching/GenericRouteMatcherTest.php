<?php

declare(strict_types=1);

namespace Tempest\Router\Tests\Routing\Matching;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Matching\GenericRouteMatcher;
use Tempest\Router\Routing\Matching\MatchingRegex;
use Tempest\Router\Tests\FakeRouteBuilder;

/**
 * @internal
 */
#[CoversClass(GenericRouteMatcher::class)]
final class GenericRouteMatcherTest extends TestCase
{
    private RouteConfig $routeConfig;

    private GenericRouteMatcher $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $routeBuilder = new FakeRouteBuilder();

        $this->routeConfig = new RouteConfig(
            [
                'GET' => [
                    '/static' => $routeBuilder->withUri('/static')->asDiscoveredRoute(),
                ],
            ],
            [
                'GET' => [
                    'b' => $routeBuilder->withUri('/dynamic/{id}')->asDiscoveredRoute(),
                    'c' => $routeBuilder->withUri('/dynamic/{id}/view')->asDiscoveredRoute(),
                    'e' => $routeBuilder->withUri('/dynamic/{id}/{tag}/{name}/{id}')->asDiscoveredRoute(),
                ],
                'PATCH' => [
                    'c' => $routeBuilder
                        ->withMethod(Method::PATCH)
                        ->withUri('/dynamic/{id}')
                        ->asDiscoveredRoute(),
                ],
            ],
            [
                'GET' => new MatchingRegex(['#^(?|/dynamic(?|/([^/]++)(?|/view\/?$(*MARK:d)|/([^/]++)(?|/([^/]++)(?|/([^/]++)\/?$(*MARK:e)))|\/?$(*MARK:b))))#']),
                'PATCH' => new MatchingRegex(['#^(?|/dynamic(?|/([^/]++)\/?$(*MARK:c)))#']),
            ],
        );

        $this->subject = new GenericRouteMatcher($this->routeConfig);
    }

    public function test_match_on_static_route(): void
    {
        $request = new ServerRequest(uri: '/static', method: 'GET');

        $matchedRoute = $this->subject->match($request);

        $this->assertEquals([], $matchedRoute->params);
        $this->assertFalse($matchedRoute->route->isDynamic);
        $this->assertEquals('/static', $matchedRoute->route->uri);
    }

    public function test_match_returns_null_on_unknown_route(): void
    {
        $request = new ServerRequest(uri: '/non-existing', method: 'GET');

        $matchedRoute = $this->subject->match($request);

        $this->assertNull($matchedRoute);
    }

    public function test_match_returns_null_on_unconfigured_method(): void
    {
        $request = new ServerRequest(uri: '/static', method: 'POST');

        $matchedRoute = $this->subject->match($request);

        $this->assertNull($matchedRoute);
    }

    public function test_match_on_dynamic_route(): void
    {
        $request = new ServerRequest(uri: '/dynamic/5', method: 'GET');

        $matchedRoute = $this->subject->match($request);

        $this->assertEquals(['id' => '5'], $matchedRoute->params);
        $this->assertTrue($matchedRoute->route->isDynamic);
        $this->assertEquals('/dynamic/{id}', $matchedRoute->route->uri);
    }

    public function test_match_on_dynamic_route_with_many_parameters(): void
    {
        $request = new ServerRequest(uri: '/dynamic/5/brendt/brent/6', method: 'GET');

        $matchedRoute = $this->subject->match($request);

        $this->assertEquals(['id' => '6', 'tag' => 'brendt', 'name' => 'brent'], $matchedRoute->params);
        $this->assertTrue($matchedRoute->route->isDynamic);
        $this->assertEquals('/dynamic/{id}/{tag}/{name}/{id}', $matchedRoute->route->uri);
    }
}
