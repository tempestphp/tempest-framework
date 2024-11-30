<?php

declare(strict_types=1);

namespace Tempest\Router\Tests\Routing\Construction;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Router\Delete;
use Tempest\Router\Patch;
use Tempest\Router\Put;
use Tempest\Router\Route;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Construction\RouteConfigurator;
use Tempest\Router\Routing\Matching\MatchingRegex;
use Tempest\Router\Tests\FakeRouteBuilder;

/**
 * @internal
 */
final class RouteConfiguratorTest extends TestCase
{
    private RouteConfigurator $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new RouteConfigurator();
    }

    public function test_empty(): void
    {
        $this->assertEquals(new RouteConfig(), $this->subject->toRouteConfig());
    }

    public function test_adding_static_routes(): void
    {
        $routeBuilder = new FakeRouteBuilder();

        $routes = [
            $routeBuilder->withMethod(Method::GET)->withUri('/1')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::POST)->withUri('/2')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::GET)->withUri('/3')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::DELETE)->withUri('/4')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::PUT)->withUri('/5')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::PATCH)->withUri('/6')->asDiscoveredRoute(),
        ];

        foreach ($routes as $route) {
            $this->subject->addRoute($route);
        }

        $config = $this->subject->toRouteConfig();

        $this->assertEquals([
            'GET' => [
                '/1' => $routes[0],
                '/1/' => $routes[0],
                '/3' => $routes[2],
                '/3/' => $routes[2],
            ],
            'POST' => [
                '/2' => $routes[1],
                '/2/' => $routes[1],
            ],
            'DELETE' => [
                '/4' => $routes[3],
                '/4/' => $routes[3],
            ],
            'PUT' => [
                '/5' => $routes[4],
                '/5/' => $routes[4],
            ],
            'PATCH' => [
                '/6' => $routes[5],
                '/6/' => $routes[5],
            ],
        ], $config->staticRoutes);
        $this->assertEquals([], $config->dynamicRoutes);
        $this->assertEquals([], $config->matchingRegexes);
    }

    public function test_adding_dynamic_routes(): void
    {
        $routeBuilder = new FakeRouteBuilder();

        $routes = [
            $routeBuilder->withMethod(Method::GET)->withUri('/dynamic/{id}')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::PATCH)->withUri('/dynamic/{id}')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::GET)->withUri('/dynamic/{id}/view')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::GET)->withUri('/dynamic/{id}/{tag}/{name}/{id}')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::DELETE)->withUri('/dynamic/{id}')->asDiscoveredRoute(),
            $routeBuilder->withMethod(Method::PUT)->withUri('/dynamic/{id}')->asDiscoveredRoute(),
        ];

        foreach ($routes as $route) {
            $this->subject->addRoute($route);
        }

        $config = $this->subject->toRouteConfig();

        $this->assertEquals([], $config->staticRoutes);
        $this->assertEquals([
            'GET' => [
                'b' => $routes[0],
                'd' => $routes[2],
                'e' => $routes[3],
            ],
            'DELETE' => [
                'f' => $routes[4],
            ],
            'PUT' => [
                'g' => $routes[5],
            ],
            'PATCH' => [
                'c' => $routes[1],
            ],
        ], $config->dynamicRoutes);

        $this->assertEquals([
            'GET' => new MatchingRegex([
                '#^(?|/dynamic(?|/([^/]++)(?|\/?$(*MARK:b)|/view\/?$(*MARK:d)|/([^/]++)(?|/([^/]++)(?|/([^/]++)\/?$(*MARK:e))))))#',
            ]),
            'DELETE' => new MatchingRegex([
                '#^(?|/dynamic(?|/([^/]++)\/?$(*MARK:f)))#',
            ]),
            'PUT' => new MatchingRegex([
                '#^(?|/dynamic(?|/([^/]++)\/?$(*MARK:g)))#',
            ]),
            'PATCH' => new MatchingRegex([
                '#^(?|/dynamic(?|/([^/]++)\/?$(*MARK:c)))#',
            ]),
        ], $config->matchingRegexes);
    }
}
