<?php

declare(strict_types=1);

namespace Tempest\Router\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Router\Route;
use Tempest\Router\RouteConfig;
use Tempest\Router\Routing\Matching\MatchingRegex;

/**
 * @internal
 */
final class RouteConfigTest extends TestCase
{
    public function test_serialization(): void
    {
        $original = new RouteConfig(
            [
                'POST' => ['/a' => new Route('/', Method::POST)],
            ],
            [
                'POST' => ['b' => new Route('/', Method::POST)],
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
