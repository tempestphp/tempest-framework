<?php

declare(strict_types=1);

namespace Tempest\Http\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Http\Route;

/**
 * @internal
 */
final class RouteTest extends TestCase
{
    #[DataProvider('uri_provider_with_param')]
    public function test_extract_parameters(string $uri, array $expectedParams): void
    {
        $route = new Route($uri, Method::GET);
        $this->assertEquals($expectedParams, $route->params);
    }

    public static function uri_provider_with_param(): Generator
    {
        yield 'static route' => ['/foo', []];
        yield 'dynamic route' => ['/foo/{bar}', ['bar']];
        yield 'dynamic route custom regex' => ['/foo/{bar:.*}', ['bar']];
        yield 'dynamic route with more parameters' => ['/{foo}/{bar}', ['foo', 'bar']];
        yield 'dynamic route with the same parameters' => ['/{bar}/{bar}', ['bar', 'bar']];
    }

    public function test_correctly_identifies_static_route(): void
    {
        $route = new Route('/foo', Method::GET);
        $this->assertFalse($route->isDynamic);
    }

    public function test_correctly_identifies_dynamic_route(): void
    {
        $route = new Route('/{foo}', Method::GET);
        $this->assertTrue($route->isDynamic);
    }

    #[DataProvider('uri_with_route_parts')]
    public function test_route_parts(string $uri, array $expectedRouteParts): void
    {
        $route = new Route($uri, Method::GET);
        $this->assertEquals($expectedRouteParts, $route->split());
    }

    public static function uri_with_route_parts(): Generator
    {
        yield 'empty' => ['', []];
        yield 'root route' => ['/', []];
        yield 'static route' => ['/foo', ['foo']];
        yield 'static route with trailing slash' => ['/foo/', ['foo']];
        yield 'route with many slashes' => ['/foo////bar//', ['foo', 'bar']];
        yield 'dynamic route' => ['/foo/{bar}', ['foo', '{bar}']];
        yield 'does not filter out 0 in routes' => ['/foo/0/bar', ['foo', '0', 'bar']];
        yield 'dynamic route custom regex' => ['/foo/{bar:.*}', ['foo', '{bar:.*}']];
        yield 'dynamic route with more parameters' => ['/{foo}/{bar}', ['{foo}', '{bar}']];
        yield 'dynamic route with the same parameters' => ['/{bar}/{bar}', ['{bar}', '{bar}']];
    }
}
