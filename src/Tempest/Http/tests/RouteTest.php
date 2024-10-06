<?php

declare(strict_types=1);

namespace Tempest\Http\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Http\Route;

/**
 * @internal
 */

final class RouteTest extends TestCase
{
    #[DataProvider('uri_provider')]
    public function test_matching_regex(string $uri, string $expected): void
    {
        $route = new Route($uri, Method::GET);
        $this->assertEquals($expected, $route->matchingRegex);
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

    public static function uri_provider(): array
    {
        return [
            'static route' => ['/foo', '/foo\/?'],
            'dynamic route' => ['/foo/{bar}', '/foo/([^/]++)\/?'],
            'dynamic route custom regex' => ['/foo/{bar:.*}', '/foo/(.*)\/?'],
            'dynamic route custom regex and nested {}' => ['/foo/{bar:a{3}}', '/foo/(a{3})\/?'],
            'dynamic route with broken custom regex' => ['/foo/{bar: {bar}}', '/foo/({bar})\/?'],
            'dynamic route custom regex and nested group' => ['/foo/{bar:id_([0-9]+)}', '/foo/(id_([0-9]+))\/?'],
        ];
    }
}
