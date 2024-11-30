<?php

declare(strict_types=1);

namespace Tempest\Router\Tests\Routing\Construction;

use PHPUnit\Framework\TestCase;
use Tempest\Http\Method;
use Tempest\Router\Routing\Construction\DuplicateRouteException;
use Tempest\Router\Routing\Construction\RoutingTree;
use Tempest\Router\Routing\Matching\MatchingRegex;
use Tempest\Router\Tests\FakeRouteBuilder;

/**
 * @internal
 */
final class RoutingTreeTest extends TestCase
{
    public function test_empty_tree(): void
    {
        $subject = new RoutingTree();
        $this->assertEquals([], $subject->toMatchingRegexes());
    }

    public function test_add_throws_on_duplicated_routes(): void
    {
        $routeBuilder = new FakeRouteBuilder();

        $subject = new RoutingTree();

        $subject->add($routeBuilder->asMarkedRoute('a'));

        $this->expectException(DuplicateRouteException::class);
        $subject->add($routeBuilder->asMarkedRoute('b'));
    }

    public function test_multiple_routes(): void
    {
        $routeBuilder = new FakeRouteBuilder();

        $subject = new RoutingTree();
        $subject->add($routeBuilder->asMarkedRoute('a'));
        $subject->add($routeBuilder->withUri('/{id}/hello/{name}')->asMarkedRoute('b'));
        $subject->add($routeBuilder->withUri('/{id}/hello/brent')->asMarkedRoute('c'));
        $subject->add($routeBuilder->withUri('/{greeting}/{name}')->asMarkedRoute('d'));
        $subject->add($routeBuilder->withUri('/{greeting}/brent')->asMarkedRoute('e'));

        $this->assertEquals([
            'GET' => new MatchingRegex([
                '#^(?|\/?$(*MARK:a)|/([^/]++)(?|/brent\/?$(*MARK:e)|/hello(?|/brent\/?$(*MARK:c)|/([^/]++)\/?$(*MARK:b))|/([^/]++)\/?$(*MARK:d)))#',
            ]),
        ], $subject->toMatchingRegexes());
    }

    public function test_chunked_routes(): void
    {
        $routeBuilder = new FakeRouteBuilder();

        $subject = new RoutingTree();
        $mark = 'a';

        for ($i = 0; $i <= 1000; $i++) {
            $mark = str_increment($mark);
            $subject->add(
                $routeBuilder->withUri("/test/{$i}/route_{$i}")->asMarkedRoute($mark)
            );
        }

        $matchingRegexes = $subject->toMatchingRegexes()['GET'];
        $this->assertGreaterThan(1, count($matchingRegexes->patterns));

        $this->assertNotNull($matchingRegexes->match('/test/0/route_0'));
        $this->assertNotNull($matchingRegexes->match('/test/1000/route_1000'));
    }

    public function test_multiple_http_methods(): void
    {
        $routeBuilder = new FakeRouteBuilder();

        $subject = new RoutingTree();
        $subject->add($routeBuilder->asMarkedRoute('a'));
        $subject->add($routeBuilder->withMethod(Method::POST)->asMarkedRoute('b'));

        $this->assertEquals([
            'GET' => new MatchingRegex(['#^\/?$(*MARK:a)#']),
            'POST' => new MatchingRegex(['#^\/?$(*MARK:b)#']),
        ], $subject->toMatchingRegexes());
    }
}
