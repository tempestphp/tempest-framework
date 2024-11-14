<?php

declare(strict_types=1);

namespace Tempest\Http\Tests\Routing\Construction;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Delete;
use Tempest\Http\Method;
use Tempest\Http\Route;
use Tempest\Http\RouteConfig;
use Tempest\Http\Routing\Construction\RouteConfigurator;

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
        $routes = [
            new Route('/1', Method::GET),
            new Route('/2', Method::POST),
            new Route('/3', Method::GET),
            new Delete('/4'),
        ];

        $this->subject->addRoute($routes[0]);
        $this->subject->addRoute($routes[1]);
        $this->subject->addRoute($routes[2]);
        $this->subject->addRoute($routes[3]);

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
        ], $config->staticRoutes);
        $this->assertEquals([], $config->dynamicRoutes);
        $this->assertEquals([], $config->matchingRegexes);
    }

    public function test_adding_dynamic_routes(): void
    {
        $routes = [
            new Route('/dynamic/{id}', Method::GET),
            new Route('/dynamic/{id}', Method::PATCH),
            new Route('/dynamic/{id}/view', Method::GET),
            new Route('/dynamic/{id}/{tag}/{name}/{id}', Method::GET),
            new Delete('/dynamic/{id}'),
        ];

        $this->subject->addRoute($routes[0]);
        $this->subject->addRoute($routes[1]);
        $this->subject->addRoute($routes[2]);
        $this->subject->addRoute($routes[3]);
        $this->subject->addRoute($routes[4]);

        $config = $this->subject->toRouteConfig();

        $this->assertEquals([], $config->staticRoutes);
        $this->assertEquals([
            'GET' => [
                'b' => $routes[0],
                'd' => $routes[2],
                'e' => $routes[3],
            ],
            'PATCH' => [
                'c' => $routes[1],
            ],
            'DELETE' => [
                'f' => $routes[4],
            ],
        ], $config->dynamicRoutes);

        $this->assertEquals([
            'GET' => '#^(?|/dynamic(?|/([^/]++)(?|/view\/?$(*MARK:d)|/([^/]++)(?|/([^/]++)(?|/([^/]++)\/?$(*MARK:e)))|\/?$(*MARK:b))))#',
            'PATCH' => '#^(?|/dynamic(?|/([^/]++)\/?$(*MARK:c)))#',
            'DELETE' => '#^(?|/dynamic(?|/([^/]++)\/?$(*MARK:f)))#',
        ], $config->matchingRegexes);
    }
}
