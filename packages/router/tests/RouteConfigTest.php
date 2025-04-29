<?php

declare(strict_types=1);

namespace Tempest\Router\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Matching\MatchingRegex;

/**
 * @internal
 */
final class RouteConfigTest extends TestCase
{
    public function test_serialization(): void
    {
        $routeBuilder = new FakeRouteBuilder(Method::POST);

        $original = new RouteConfig(
            [
                'POST' => ['/a' => $routeBuilder->asDiscoveredRoute()],
            ],
            [
                'POST' => ['b' => $routeBuilder->asDiscoveredRoute()],
            ],
            [
                'POST' => new MatchingRegex(['#^(?|/([^/]++)(?|/1\/?$(*MARK:b)|/3\/?$(*MARK:d)))#']),
            ],
        );

        $serialized = serialize($original);
        /** @var RouteConfig $deserialized */
        $deserialized = unserialize($serialized);

        $this->assertEquals($original, $deserialized);
    }
}
